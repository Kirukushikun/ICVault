<?php

namespace App\Services;

use App\Enums\ImportStatus;
use App\Models\ImportBatch;
use App\Models\Question;
use Illuminate\Support\Collection;
use RuntimeException;

class ImportPipelineService
{
    /**
     * uploaded -> parsed -> ai_converted -> queued -> imported, with a
     * failure exit from any non-terminal state.
     */
    private const array ALLOWED_TRANSITIONS = [
        'uploaded' => ['parsed', 'failed'],
        'parsed' => ['ai_converted', 'failed'],
        'ai_converted' => ['queued', 'failed'],
        'queued' => ['imported', 'failed'],
        'imported' => [],
        'failed' => [],
    ];

    public function transitionTo(ImportBatch $batch, ImportStatus $status): ImportBatch
    {
        $allowed = self::ALLOWED_TRANSITIONS[$batch->status->value];

        if (! in_array($status->value, $allowed, true)) {
            throw new RuntimeException(
                "Cannot transition import batch from {$batch->status->value} to {$status->value}."
            );
        }

        $batch->update(['status' => $status]);

        return $batch;
    }

    /**
     * Turns parser candidates into real Question rows and marks the batch
     * imported. Requires the batch to already be `queued`.
     *
     * @param  array<int, array{difficulty: string, type: string, prompt: string, options_json: ?array, answer: string, explanation: ?string}>  $candidates
     * @return Collection<int, Question>
     */
    public function importQuestions(ImportBatch $batch, array $candidates): Collection
    {
        $questions = collect($candidates)->map(fn (array $candidate) => Question::create([
            ...$candidate,
            'category_id' => $batch->category_id,
            'import_batch_id' => $batch->id,
        ]));

        $this->transitionTo($batch, ImportStatus::Imported);

        return $questions;
    }
}
