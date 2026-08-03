<?php

namespace App\Tools\Quiz\Services;

use App\Tools\Quiz\Enums\Difficulty;
use App\Tools\Quiz\Enums\QuestionType;
use App\Tools\Quiz\Services\AI\QuestionParserContract;
use RuntimeException;

/**
 * Deterministic, no-AI question parser for hand-authored Markdown. Every
 * question starts with a `Q:` line; the shape of what follows decides its
 * type — no explicit tag needed:
 *
 *   Q: What does PSR-4 define?
 *   a) Autoloading standard
 *   b) Coding style guide
 *   Answer: a                    → multiple_choice (lettered options present)
 *
 *   Q: Write a function that reverses a string.
 *   ```php
 *   function reverse(string $s): string { return strrev($s); }
 *   ```                          → code (a fenced block, no lettered options)
 *
 *   Q: The ____ directive escapes output in Blade.
 *   Answer: {{ }}                → fill_blank (plain Answer:, no fence/options)
 *
 * Optional `Explanation:` and `Difficulty: easy|medium|hard` lines work in
 * any block. Blank lines and `---` lines are pure formatting and ignored.
 *
 * Unlike {@see \App\Tools\Quiz\Services\AI\ClaudeQuestionParser}, this never
 * silently drops a question the reader wrote by hand just because a detail
 * is off (e.g. an `Answer:` letter that doesn't match any listed option) —
 * it surfaces that candidate with whatever it could extract, so the review
 * screen's own validation catches it and lets the reader fix or discard it.
 * It only drops a block when there's nothing coherent to show at all: no
 * prompt, no answer, or no way to tell what type it is.
 */
class MarkdownQuestionParser implements QuestionParserContract
{
    private const string Q_LINE = '/^q:\s*(.*)$/i';

    private const string OPTION_LINE = '/^([a-z])[).]\s*(.*)$/i';

    private const string ANSWER_LINE = '/^answer:\s*(.*)$/i';

    private const string EXPLANATION_LINE = '/^explanation:\s*(.*)$/i';

    private const string DIFFICULTY_LINE = '/^difficulty:\s*(.*)$/i';

    private const string SEPARATOR_LINE = '/^-{3,}$/';

    public function parse(string $rawContent): array
    {
        $blocks = $this->splitIntoBlocks($rawContent);

        if ($blocks === []) {
            throw new RuntimeException(
                'No questions found. Each one must start with a line beginning "Q:" — '
                .'see the Structured Markdown format guide on this page.'
            );
        }

        $candidates = collect($blocks)
            ->map(fn (array $lines) => $this->parseBlock($lines))
            ->filter()
            ->values()
            ->all();

        if ($candidates === []) {
            throw new RuntimeException('None of the "Q:" blocks in this file had both a prompt and an answer.');
        }

        return $candidates;
    }

    /**
     * Splits raw text into one line-array per question, keyed off `Q:`
     * lines rather than blank lines or `---` — that way stray formatting
     * between questions never matters, and a `Q:`-looking line inside a
     * fenced code answer never starts a false new question.
     *
     * @return array<int, array<int, string>>
     */
    private function splitIntoBlocks(string $rawContent): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $rawContent);

        $blocks = [];
        $current = [];
        $inFence = false;
        $started = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($inFence) {
                $current[] = $line;
                if ($trimmed === '```') {
                    $inFence = false;
                }

                continue;
            }

            if (str_starts_with($trimmed, '```')) {
                $current[] = $line;
                $inFence = true;

                continue;
            }

            if (preg_match(self::Q_LINE, $trimmed)) {
                if ($started) {
                    $blocks[] = $current;
                }
                $current = [$line];
                $started = true;

                continue;
            }

            if ($started) {
                $current[] = $line;
            }
        }

        if ($started) {
            $blocks[] = $current;
        }

        return $blocks;
    }

    /**
     * @param  array<int, string>  $lines
     * @return array{difficulty: string, type: string, prompt: string, options_json: ?array, answer: string, explanation: ?string}|null
     */
    private function parseBlock(array $lines): ?array
    {
        preg_match(self::Q_LINE, trim($lines[0]), $m);
        $promptLines = [trim($m[1] ?? '')];

        /** @var array<string, string> $options letter => text, in encounter order */
        $options = [];
        $codeLines = null; // null until a fence opens; array once it has
        $inFence = false;
        $answerRaw = null;
        $explanationRaw = null;
        $difficultyRaw = null;
        $promptPhaseOver = false;

        foreach (array_slice($lines, 1) as $line) {
            $trimmed = trim($line);

            if ($inFence) {
                if ($trimmed === '```') {
                    $inFence = false;
                } else {
                    $codeLines[] = $line;
                }

                continue;
            }

            if ($trimmed === '' || preg_match(self::SEPARATOR_LINE, $trimmed)) {
                continue;
            }

            if (preg_match(self::OPTION_LINE, $trimmed, $m)) {
                $options[strtolower($m[1])] = trim($m[2]);
                $promptPhaseOver = true;

                continue;
            }

            if (str_starts_with($trimmed, '```')) {
                $inFence = true;
                $codeLines ??= [];
                $promptPhaseOver = true;

                continue;
            }

            if (preg_match(self::ANSWER_LINE, $trimmed, $m)) {
                $answerRaw = trim($m[1]);
                $promptPhaseOver = true;

                continue;
            }

            if (preg_match(self::EXPLANATION_LINE, $trimmed, $m)) {
                $explanationRaw = trim($m[1]);
                $promptPhaseOver = true;

                continue;
            }

            if (preg_match(self::DIFFICULTY_LINE, $trimmed, $m)) {
                $difficultyRaw = trim($m[1]);
                $promptPhaseOver = true;

                continue;
            }

            if (! $promptPhaseOver) {
                $promptLines[] = $trimmed;
            }
            // Stray text after the block's structure has already started
            // (e.g. trailing notes) is ignored rather than erroring.
        }

        $type = match (true) {
            $options !== [] => QuestionType::MultipleChoice,
            $codeLines !== null => QuestionType::Code,
            $answerRaw !== null => QuestionType::FillBlank,
            default => null,
        };

        $prompt = trim(implode(' ', array_filter($promptLines, fn ($l) => $l !== '')));

        $answer = match ($type) {
            QuestionType::MultipleChoice => $this->resolveMultipleChoiceAnswer($answerRaw, $options),
            QuestionType::Code => trim(implode("\n", $codeLines ?? [])),
            QuestionType::FillBlank => trim((string) $answerRaw),
            default => '',
        };

        if (! $type || $prompt === '' || $answer === '') {
            return null;
        }

        return [
            'difficulty' => (Difficulty::tryFrom(strtolower((string) $difficultyRaw)) ?? Difficulty::Medium)->value,
            'type' => $type->value,
            'prompt' => $prompt,
            'options_json' => $type === QuestionType::MultipleChoice ? array_values($options) : null,
            'answer' => $answer,
            'explanation' => $explanationRaw !== null && $explanationRaw !== '' ? $explanationRaw : null,
        ];
    }

    /**
     * If `Answer:` names a letter that's actually among the parsed options,
     * resolve it to that option's text — the normal case. If `Answer:` is
     * present but doesn't resolve (typo'd letter, or one that was never
     * listed), the raw text is kept as-is rather than discarded: it will
     * plainly not match any option, which the review screen's save-time
     * validation will flag for the reader to fix, instead of the question
     * vanishing with no explanation.
     *
     * @param  array<string, string>  $options
     */
    private function resolveMultipleChoiceAnswer(?string $answerRaw, array $options): string
    {
        if ($answerRaw === null) {
            return '';
        }

        $letter = strtolower(trim($answerRaw));

        return $options[$letter] ?? trim($answerRaw);
    }
}
