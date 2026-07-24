<?php

namespace App\Jobs;

use App\Models\Question;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Danger-zone job: wipes the entire question pool. Attempts cascade-delete
 * with their questions; Tips are nulled out via nullOnDelete rather than
 * deleted, so any surviving hand-authored context isn't lost.
 */
class ResetQuestionPool implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Question::query()->delete();
    }
}
