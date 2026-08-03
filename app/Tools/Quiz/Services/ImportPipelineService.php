<?php

namespace App\Tools\Quiz\Services;

use App\Tools\Quiz\Enums\ImportStatus;
use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\Tip;
use Illuminate\Support\Collection;
use RuntimeException;

class ImportPipelineService
{
    /**
     * uploaded -> parsed -> ready -> imported, with a failure exit from any
     * non-terminal state. `ready` is where a batch sits, with its candidates
     * staged in `candidates_json`, while the reader reviews and edits them —
     * nothing becomes a Question row until they explicitly save.
     */
    private const array ALLOWED_TRANSITIONS = [
        'uploaded' => ['parsed', 'failed'],
        'parsed' => ['ready', 'failed'],
        'ready' => ['imported', 'failed'],
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
     * Stages parser output on the batch for review and marks it `ready` —
     * the pause between parsing and committing anything to the questions
     * table, so a reader can edit or discard candidates before they exist as
     * real rows. Requires the batch to already be `parsed`.
     */
    public function stageCandidates(ImportBatch $batch, array $candidates): ImportBatch
    {
        $batch->update(['candidates_json' => $candidates]);

        return $this->transitionTo($batch, ImportStatus::Ready);
    }

    /**
     * Turns (possibly reader-edited) candidates into real Question rows and
     * marks the batch imported. Requires the batch to already be `ready`.
     * Takes `$candidates` as a parameter rather than reading them off the
     * batch, so it works equally for the original parser output or whatever
     * the reader changed on the review screen.
     *
     * @param  array<int, array{difficulty: string, type: string, prompt: string, options_json: ?array, answer: string, explanation: ?string}>  $candidates
     * @return Collection<int, Question>
     */
    public function importQuestions(ImportBatch $batch, array $candidates): Collection
    {
        $questions = $this->commitQuestions($batch, $candidates);

        $this->transitionTo($batch, ImportStatus::Imported);

        return $questions;
    }

    /**
     * Creates Question rows without closing the batch — the review screen
     * saves candidates one at a time, so the batch has to stay `ready` until
     * the reader has actually dealt with the last one. Callers are
     * responsible for the eventual transition to `imported`.
     *
     * @param  array<int, array{difficulty: string, type: string, prompt: string, options_json: ?array, answer: string, explanation: ?string}>  $candidates
     * @return Collection<int, Question>
     */
    public function commitQuestions(ImportBatch $batch, array $candidates): Collection
    {
        return collect($candidates)->map(function (array $candidate) use ($batch) {
            $question = Question::create([
                ...$candidate,
                'category_id' => $batch->category_id,
                'import_batch_id' => $batch->id,
            ]);

            $this->spinTip($question);

            return $question;
        });
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
