<?php

namespace App\Tools\Quiz\Services\AI;

use App\Tools\Quiz\Enums\Difficulty;
use App\Tools\Quiz\Enums\QuestionType;

/**
 * Deterministic stand-in for a real AI parser: turns each non-empty line of
 * raw note/log text into a fill-blank question by blanking out its last
 * word. Swap for a real provider behind {@see QuestionParserContract}
 * without touching the pipeline or job that calls it.
 */
class NaiveLineParser implements QuestionParserContract
{
    public function parse(string $rawContent): array
    {
        $lines = collect(preg_split('/\r?\n/', trim($rawContent)))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values();

        return $lines->map(function (string $line) {
            $words = preg_split('/\s+/', $line);
            $answer = end($words);

            return [
                'difficulty' => Difficulty::Medium->value,
                'type' => QuestionType::FillBlank->value,
                'prompt' => 'Fill in the blank: '.preg_replace('/'.preg_quote($answer, '/').'$/', '____', $line),
                'options_json' => null,
                'answer' => $answer,
                'explanation' => $line,
            ];
        })->all();
    }
}
