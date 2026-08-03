<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Services\AI\ClaudeQuestionParser;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class ClaudeQuestionParserTest extends TestCase
{
    /** Anthropic's tool_use shape: `input` is already a parsed object, unlike OpenAI's JSON-string arguments. */
    private function toolResponse(array $questions): array
    {
        return [
            'content' => [
                ['type' => 'text', 'text' => "Sure, here you go:\n"],
                ['type' => 'tool_use', 'name' => 'emit_questions', 'input' => ['questions' => $questions]],
            ],
        ];
    }

    private function enable(array $extra = []): void
    {
        config(array_merge([
            'services.anthropic.key' => 'sk-ant-test',
            'services.anthropic.model' => 'claude-sonnet-5',
            'services.anthropic.enabled' => true,
        ], $extra));
    }

    public function test_it_sends_the_raw_content_and_a_forced_tool_call(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response($this->toolResponse([
                ['difficulty' => 'medium', 'type' => 'fill_blank', 'prompt' => 'Blade compiles to plain ____',
                    'answer' => 'PHP', 'explanation' => 'Views are cached as compiled PHP.'],
            ])),
        ]);

        (new ClaudeQuestionParser)->parse('Blade compiles to plain PHP.');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->hasHeader('x-api-key', 'sk-ant-test')
                && $request->hasHeader('anthropic-version', '2023-06-01')
                && $request['model'] === 'claude-sonnet-5'
                && $request['system'] !== null
                && $request['messages'][0] === ['role' => 'user', 'content' => 'Blade compiles to plain PHP.']
                && $request['tool_choice'] === ['type' => 'tool', 'name' => 'emit_questions'];
        });
    }

    public function test_it_normalizes_a_well_formed_response(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response($this->toolResponse([
                ['difficulty' => 'hard', 'type' => 'multiple_choice',
                    'prompt' => 'Which directive escapes output in Blade?',
                    'options' => ['{{ }}', '{!! !!}', '@php', '@raw'],
                    'answer' => '{{ }}', 'explanation' => '{!! !!} skips escaping.',
                    'source_excerpt' => 'Blade escapes with {{ }} by default.'],
            ])),
        ]);

        $result = (new ClaudeQuestionParser)->parse('...');

        $this->assertSame([[
            'difficulty' => 'hard',
            'type' => 'multiple_choice',
            'prompt' => 'Which directive escapes output in Blade?',
            'options_json' => ['{{ }}', '{!! !!}', '@php', '@raw'],
            'answer' => '{{ }}',
            'explanation' => '{!! !!} skips escaping.',
            'source_excerpt' => 'Blade escapes with {{ }} by default.',
        ]], $result);
    }

    /**
     * The excerpt is a review aid, not part of the question — a model that
     * omits it should still produce a usable candidate.
     */
    public function test_a_missing_source_excerpt_does_not_drop_the_question(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response($this->toolResponse([
                ['difficulty' => 'easy', 'type' => 'fill_blank', 'prompt' => 'Blade compiles to plain ____',
                    'answer' => 'PHP', 'explanation' => null],
            ])),
        ]);

        $result = (new ClaudeQuestionParser)->parse('...');

        $this->assertCount(1, $result);
        $this->assertNull($result[0]['source_excerpt']);
    }

    public function test_it_asks_for_the_source_excerpt_in_the_tool_schema(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response($this->toolResponse([
                ['difficulty' => 'easy', 'type' => 'fill_blank', 'prompt' => 'x ____', 'answer' => 'y', 'explanation' => null],
            ])),
        ]);

        (new ClaudeQuestionParser)->parse('...');

        Http::assertSent(function ($request) {
            $item = $request['tools'][0]['input_schema']['properties']['questions']['items'];

            return array_key_exists('source_excerpt', $item['properties'])
                && in_array('source_excerpt', $item['required'], true);
        });
    }

    /**
     * Every candidate — AI or manual — passes through a review screen before
     * it touches the questions table, so an MC answer that doesn't match its
     * own options is no longer a reason to drop it silently: it's surfaced,
     * and the review screen's save-time validation catches the mismatch.
     */
    public function test_a_multiple_choice_answer_not_among_its_options_is_surfaced_not_dropped(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response($this->toolResponse([
                ['difficulty' => 'medium', 'type' => 'multiple_choice', 'prompt' => 'Mismatched one',
                    'options' => ['a', 'b'], 'answer' => 'c', 'explanation' => null],
            ])),
        ]);

        $result = (new ClaudeQuestionParser)->parse('...');

        $this->assertCount(1, $result);
        $this->assertSame('c', $result[0]['answer']);
        $this->assertSame(['a', 'b'], $result[0]['options_json']);
    }

    public function test_an_unknown_difficulty_falls_back_to_medium_rather_than_dropping_the_question(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response($this->toolResponse([
                ['difficulty' => 'brutal', 'type' => 'code', 'prompt' => 'Reverse a string',
                    'answer' => 'strrev($s)', 'explanation' => null],
            ])),
        ]);

        $result = (new ClaudeQuestionParser)->parse('...');

        $this->assertSame('medium', $result[0]['difficulty']);
    }

    public function test_it_throws_rather_than_import_nothing_when_every_candidate_is_unusable(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response($this->toolResponse([
                ['difficulty' => 'medium', 'type' => 'fill_blank', 'prompt' => '', 'answer' => '', 'explanation' => null],
            ])),
        ]);

        $this->expectException(RuntimeException::class);

        (new ClaudeQuestionParser)->parse('...');
    }

    /**
     * A truncated tool call still arrives as HTTP 200 with a partial list, so
     * the only signal that questions went missing is `stop_reason`.
     */
    public function test_hitting_the_output_ceiling_fails_rather_than_importing_a_partial_set(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'stop_reason' => 'max_tokens',
                ...$this->toolResponse([
                    ['difficulty' => 'easy', 'type' => 'fill_blank', 'prompt' => 'survived ____', 'answer' => 'this', 'explanation' => null],
                ]),
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Split it into smaller notes');

        (new ClaudeQuestionParser)->parse('a very long note');
    }

    public function test_a_normal_tool_use_stop_reason_is_not_treated_as_truncation(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'stop_reason' => 'tool_use',
                ...$this->toolResponse([
                    ['difficulty' => 'easy', 'type' => 'fill_blank', 'prompt' => 'x ____', 'answer' => 'y', 'explanation' => null],
                ]),
            ]),
        ]);

        $this->assertCount(1, (new ClaudeQuestionParser)->parse('...'));
    }

    public function test_it_fails_fast_and_clearly_when_generation_is_disabled(): void
    {
        $this->enable(['services.anthropic.enabled' => false]);
        Http::fake();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('AI_IMPORT_ENABLED');

        (new ClaudeQuestionParser)->parse('...');

        Http::assertNothingSent();
    }

    public function test_it_fails_fast_and_clearly_when_no_key_is_configured(): void
    {
        $this->enable(['services.anthropic.key' => null]);
        Http::fake();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ANTHROPIC_API_KEY');

        (new ClaudeQuestionParser)->parse('...');

        Http::assertNothingSent();
    }

    /** @return array<string, array{int, string}> */
    public static function statusMessages(): array
    {
        return [
            'billing/credits exhausted' => [402, 'billing needs attention'],
            'rate limited' => [429, 'rate-limiting'],
            'bad key' => [401, 'rejected the API key'],
            'permission error' => [403, 'rejected the API key'],
            'overloaded' => [529, 'temporarily overloaded'],
        ];
    }

    #[DataProvider('statusMessages')]
    public function test_status_codes_get_a_message_the_reader_can_act_on(int $status, string $expectedSubstring): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => ['type' => 'x', 'message' => 'provider detail']], $status),
        ]);

        try {
            (new ClaudeQuestionParser)->parse('...');
            $this->fail('Expected a RuntimeException.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString($expectedSubstring, $e->getMessage());
        }
    }

    public function test_an_unmapped_error_status_falls_back_to_the_providers_own_message(): void
    {
        $this->enable();
        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => ['type' => 'invalid_request_error', 'message' => 'model not found']], 400),
        ]);

        try {
            (new ClaudeQuestionParser)->parse('...');
            $this->fail('Expected a RuntimeException.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('model not found', $e->getMessage());
        }
    }
}
