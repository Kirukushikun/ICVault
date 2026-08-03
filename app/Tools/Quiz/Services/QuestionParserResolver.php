<?php

namespace App\Tools\Quiz\Services;

use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Services\AI\QuestionParserContract;

/**
 * Picks which parser handles a batch. Not itself container-bound as
 * {@see QuestionParserContract} — there are now two real implementations,
 * selected per-batch by how it was submitted, rather than one global swap.
 */
class QuestionParserResolver
{
    public function resolve(ImportBatch $batch): QuestionParserContract
    {
        return match ($batch->source_type) {
            'markdown' => app(MarkdownQuestionParser::class),
            default => app(QuestionParserContract::class),
        };
    }
}
