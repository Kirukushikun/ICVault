<?php

namespace App\Services;

use App\Enums\ImportStatus;
use App\Models\ImportBatch;
use App\Models\Question;
use App\Models\Tip;
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
        $questions = collect($candidates)->map(function (array $candidate) use ($batch) {
            $question = Question::create([
                ...$candidate,
                'category_id' => $batch->category_id,
                'import_batch_id' => $batch->id,
            ]);

            $this->spinTip($question);

            return $question;
        });

        $this->transitionTo($batch, ImportStatus::Imported);

        return $questions;
    }

    /**
     * Every generated question with an explanation doubles as a Did-You-Know
     * candidate — cheap to create, and Dashboard only ever surfaces one at
     * random per category, so duplicates across imports aren't a problem.
     */
    private function spinTip(Question $question): void
    {
        if (! $question->explanation) {
            return;
        }

        Tip::create([
            'category_id' => $question->category_id,
            'body' => $question->explanation,
            'source_question_id' => $question->id,
        ]);
    }
}
