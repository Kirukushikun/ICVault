<?php

namespace App\Jobs;

use App\Enums\MasteryState;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\QuizSession;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Danger-zone job: "Clear Progress Data" from the mockup — wipes mastery
 * scores and session history but leaves the question pool itself intact.
 */
class ResetMasteryProgress implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        DB::transaction(function () {
            Question::query()->update([
                'mastery_state' => MasteryState::New->value,
                'mastery_streak' => 0,
                'last_reviewed_at' => null,
            ]);

            Attempt::query()->delete();
            QuizSession::query()->delete();
        });
    }
}
