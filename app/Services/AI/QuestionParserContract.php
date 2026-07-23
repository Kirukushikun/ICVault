<?php

namespace App\Services\AI;

interface QuestionParserContract
{
    /**
     * Turn raw note/log text into candidate question rows, ready for
     * `Question::create()` (minus category_id/import_batch_id, which the
     * caller attaches).
     *
     * @return array<int, array{difficulty: string, type: string, prompt: string, options_json: ?array, answer: string, explanation: ?string}>
     */
    public function parse(string $rawContent): array;
}
