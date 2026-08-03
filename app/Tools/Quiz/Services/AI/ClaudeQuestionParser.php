<?php

namespace App\Tools\Quiz\Services\AI;

use App\Tools\Quiz\Enums\Difficulty;
use App\Tools\Quiz\Enums\QuestionType;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Turns raw notes into quiz questions with a real model instead of the
 * blank-the-last-word placeholder ({@see NaiveLineParser}). Calls Anthropic's
 * Messages API directly and forces structured output through tool use, so
 * the response is guaranteed to match the schema instead of hoping the model
 * didn't wrap it in a code fence or a "Sure, here are your questions:"
 * preamble.
 *
 * Every candidate this returns — AI-generated or not — passes through a
 * review screen before it ever becomes a Question row (see ImportPage), so
 * this class only drops a candidate when there's nothing coherent to show a
 * reviewer (empty prompt/answer, unrecognized type). A multiple-choice
 * candidate whose answer doesn't match any of its own options is still
 * surfaced rather than silently dropped — the review screen's save-time
 * validation catches that uniformly for both this parser and the manual
 * Markdown one, so there's no reason to duplicate the check here.
 */
class ClaudeQuestionParser implements QuestionParserContract
{
    private const string ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private const string ANTHROPIC_VERSION = '2023-06-01';

    private const string TOOL_NAME = 'emit_questions';

    /**
     * The ceiling on generated questions, not on the note being read — a
     * long note costs input tokens (cheap, and nowhere near the context
     * window) while every question written costs output. Roughly 150 tokens
     * per question with its excerpt, so this affords ~50 from one note.
     */
    private const int MAX_TOKENS = 8192;

    private const string SYSTEM_PROMPT = <<<'PROMPT'
        You write spaced-repetition quiz questions from a learner's raw notes
        or session logs. Read the input and produce a mix of question types
        that actually test understanding of what's there — don't pad with
        trivial questions just to hit a count, and don't invent facts the
        input doesn't support.

        For each question:
        - type "multiple_choice": write exactly 4 plausible options, one of
          which is the answer verbatim. Wrong options should be plausible
          confusions, not obviously wrong filler.
        - type "fill_blank": the prompt contains a "____" where the answer
          goes; answer is the exact word or phrase that fills it.
        - type "code": prompt asks for a short snippet or command; answer is
          a reference solution, not a rubric.
        - difficulty reflects how hard the underlying concept is to recall,
          not how long the question is.
        - explanation is one or two sentences a learner would want after
          answering — the "why", not a restatement of the question.
        - source_excerpt is the sentence or two from the input this question
          came from, quoted verbatim so the reader can check the question
          against what they actually wrote. Never paraphrase it, and never
          write one for a claim the input doesn't contain.

        Skip content too thin or too vague to make a fair question from.
        PROMPT;

    public function parse(string $rawContent): array
    {
        if (! config('services.anthropic.enabled')) {
            throw new RuntimeException(
                'AI question generation is turned off (AI_IMPORT_ENABLED=false in .env).'
            );
        }

        $key = config('services.anthropic.key');

        if (! $key) {
            throw new RuntimeException(
                'ANTHROPIC_API_KEY is not configured — set it in .env to enable question generation.'
            );
        }

        $response = Http::withHeaders([
            'x-api-key' => $key,
            'anthropic-version' => self::ANTHROPIC_VERSION,
        ])
            // A synchronous request has no polling/progress fallback if this
            // runs long, so this stays comfortably under a minute rather
            // than the 2 minutes a background worker could afford to wait.
            ->timeout(60)
            ->post(self::ENDPOINT, [
                'model' => config('services.anthropic.model'),
                'max_tokens' => self::MAX_TOKENS,
                'system' => self::SYSTEM_PROMPT,
                'messages' => [
                    ['role' => 'user', 'content' => $rawContent],
                ],
                'tools' => [$this->toolDefinition()],
                'tool_choice' => ['type' => 'tool', 'name' => self::TOOL_NAME],
            ]);

        if ($response->failed()) {
            throw new RuntimeException($this->describeFailure($response));
        }

        $body = $response->json();

        // Hitting the output ceiling truncates the tool call mid-JSON, so
        // whatever survives is an arbitrary prefix of the questions asked
        // for. Silently importing a partial set would look like a complete
        // one, so this fails loudly and names the fix.
        if (($body['stop_reason'] ?? null) === 'max_tokens') {
            throw new RuntimeException(
                'This note produced more questions than fit in one response. Split it into '
                .'smaller notes and import them separately.'
            );
        }

        $questions = $this->extractToolInput($body)['questions'] ?? null;

        if (! is_array($questions)) {
            throw new RuntimeException('Claude response did not contain a questions array.');
        }

        $candidates = collect($questions)
            ->map(fn ($raw) => $this->normalize($raw))
            ->filter()
            ->values()
            ->all();

        if ($candidates === []) {
            throw new RuntimeException('Claude returned no usable questions for this input.');
        }

        return $candidates;
    }

    /**
     * A failed batch shows `error_message` verbatim in the import screen, so
     * this is the one place that decides what the reader actually sees when
     * generation doesn't work — a raw HTTP exception message is a stack
     * trace, not an explanation. Codes and error `type` values per
     * Anthropic's documented error shape.
     */
    private function describeFailure(Response $response): string
    {
        $status = $response->status();
        $providerMessage = $response->json('error.message');

        return match ($status) {
            402 => 'Your Anthropic billing needs attention — check payment details at platform.claude.com, '
                .'then try this import again.',
            429 => 'Anthropic is rate-limiting requests right now. Wait a moment and try this import again.',
            401, 403 => 'Anthropic rejected the API key. Check ANTHROPIC_API_KEY in .env.',
            529 => "Anthropic's API is temporarily overloaded. Try this import again shortly.",
            default => "Anthropic request failed (HTTP {$status})".($providerMessage ? ": {$providerMessage}" : '.'),
        };
    }

    /** @return array<string, mixed> */
    private function toolDefinition(): array
    {
        return [
            'name' => self::TOOL_NAME,
            'description' => 'Return the generated quiz questions.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'questions' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'difficulty' => ['type' => 'string', 'enum' => array_column(Difficulty::cases(), 'value')],
                                'type' => ['type' => 'string', 'enum' => array_column(QuestionType::cases(), 'value')],
                                'prompt' => ['type' => 'string'],
                                'options' => ['type' => ['array', 'null'], 'items' => ['type' => 'string']],
                                'answer' => ['type' => 'string'],
                                'explanation' => ['type' => 'string'],
                                'source_excerpt' => ['type' => 'string'],
                            ],
                            'required' => ['difficulty', 'type', 'prompt', 'answer', 'explanation', 'source_excerpt'],
                        ],
                    ],
                ],
                'required' => ['questions'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function extractToolInput(array $body): array
    {
        foreach ($body['content'] ?? [] as $block) {
            if (($block['type'] ?? null) === 'tool_use' && ($block['name'] ?? null) === self::TOOL_NAME) {
                return $block['input'] ?? [];
            }
        }

        return [];
    }

    /**
     * @param  mixed  $raw
     * @return array{difficulty: string, type: string, prompt: string, options_json: ?array, answer: string, explanation: ?string, source_excerpt: ?string}|null
     */
    private function normalize($raw): ?array
    {
        if (! is_array($raw)) {
            return null;
        }

        $type = QuestionType::tryFrom($raw['type'] ?? '');
        $prompt = trim((string) ($raw['prompt'] ?? ''));
        $answer = trim((string) ($raw['answer'] ?? ''));

        if (! $type || $prompt === '' || $answer === '') {
            return null;
        }

        $options = null;

        if ($type === QuestionType::MultipleChoice) {
            $options = collect($raw['options'] ?? [])
                ->map(fn ($o) => trim((string) $o))
                ->filter(fn ($o) => $o !== '')
                ->values()
                ->all();
        }

        return [
            'difficulty' => (Difficulty::tryFrom($raw['difficulty'] ?? '') ?? Difficulty::Medium)->value,
            'type' => $type->value,
            'prompt' => $prompt,
            'options_json' => $options,
            'answer' => $answer,
            'explanation' => trim((string) ($raw['explanation'] ?? '')) ?: null,
            'source_excerpt' => trim((string) ($raw['source_excerpt'] ?? '')) ?: null,
        ];
    }
}
