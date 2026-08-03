<?php

namespace App\Tools\Quiz;

use App\Platform\Contracts\ProvidesRecentActivity;
use App\Platform\Contracts\ProvidesToolSummary;
use App\Platform\Support\ActivityItem;
use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\QuizSession;

/**
 * The Quiz tool's public face to the platform. Everything the Hub knows about
 * this tool comes through here, so the shell never queries quiz tables itself.
 */
final class QuizTool implements ProvidesRecentActivity, ProvidesToolSummary
{
    public function summary(): ?string
    {
        $questions = Question::count();

        if ($questions === 0) {
            return 'No questions yet — import some notes to start';
        }

        $label = $questions.' card'.($questions === 1 ? '' : 's');

        // Read-only on purpose: QuizSession::today() would create today's row,
        // and merely looking at the Hub shouldn't start a session.
        $session = QuizSession::whereDate('date', today())->first();

        if ($session === null) {
            return $label.' · nothing answered today';
        }

        $done = min($session->completed_count, $session->quota);

        return $label.' · '.$done.'/'.$session->quota.' today';
    }

    /** @return array<int, ActivityItem> */
    public function recentActivity(int $limit = 5): array
    {
        $sessions = QuizSession::query()
            ->where('completed_count', '>', 0)
            ->latest('date')
            ->limit($limit)
            ->get()
            ->map(fn (QuizSession $session) => new ActivityItem(
                text: 'Answered '.$session->completed_count.' of '.$session->quota.' in the daily quota',
                at: $session->date,
            ));

        $imports = ImportBatch::query()
            ->withCount('questions')
            // has() not having() — withCount adds a subquery column, not a
            // GROUP BY aggregate, so HAVING has nothing to filter against.
            ->has('questions')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (ImportBatch $batch) => new ActivityItem(
                text: 'Imported '.$batch->questions_count.' new question'.($batch->questions_count === 1 ? '' : 's')
                    .' from '.match ($batch->source_type) {
                        'note' => 'an Obsidian note',
                        'markdown' => 'a structured Markdown file',
                        default => 'a session log',
                    },
                at: $batch->created_at,
            ));

        return $sessions->concat($imports)->all();
    }
}
