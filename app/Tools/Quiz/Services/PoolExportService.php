<?php

namespace App\Tools\Quiz\Services;

use App\Tools\Quiz\Models\Question;

class PoolExportService
{
    /**
     * @return array{exported_at: string, questions: array<int, array<string, mixed>>}
     */
    public function build(): array
    {
        $questions = Question::with('category')->get()->map(fn (Question $question) => [
            'category_slug' => $question->category?->slug,
            'category_name' => $question->category?->name,
            'category_color' => $question->category?->color,
            'difficulty' => $question->difficulty->value,
            'type' => $question->type->value,
            'prompt' => $question->prompt,
            'options' => $question->options_json,
            'answer' => $question->answer,
            'explanation' => $question->explanation,
        ])->all();

        return [
            'exported_at' => now()->toIso8601String(),
            'questions' => $questions,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->build(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function toCsv(): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['category', 'difficulty', 'type', 'prompt', 'options', 'answer', 'explanation']);

        foreach ($this->build()['questions'] as $question) {
            fputcsv($handle, [
                $question['category_name'],
                $question['difficulty'],
                $question['type'],
                $question['prompt'],
                $question['options'] ? implode(' | ', $question['options']) : '',
                $question['answer'],
                $question['explanation'],
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Structured Markdown, in the exact "Q: / a) / Answer:" grammar
     * {@see \App\Tools\Quiz\Services\MarkdownQuestionParser} reads — so an
     * export can be re-imported through the Ready-made tab unchanged.
     */
    public function toMarkdown(): string
    {
        $sections = collect($this->build()['questions'])->map(function (array $q) {
            $lines = ["Q: {$q['prompt']}"];

            if ($q['type'] === 'multiple_choice') {
                foreach (array_values($q['options'] ?? []) as $i => $option) {
                    $lines[] = chr(97 + $i).") {$option}";
                }
                $letter = chr(97 + array_search($q['answer'], $q['options'] ?? [], true));
                $lines[] = "Answer: {$letter}";
            } elseif ($q['type'] === 'code') {
                $lines[] = '```';
                $lines[] = $q['answer'];
                $lines[] = '```';
            } else {
                $lines[] = "Answer: {$q['answer']}";
            }

            if ($q['explanation']) {
                $lines[] = "Explanation: {$q['explanation']}";
            }

            $lines[] = "Difficulty: {$q['difficulty']}";

            return implode("\n", $lines);
        });

        return $sections->implode("\n\n---\n\n");
    }
}
