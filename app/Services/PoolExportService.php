<?php

namespace App\Services;

use App\Models\Question;

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
}
