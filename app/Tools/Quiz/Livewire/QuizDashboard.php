<?php

namespace App\Tools\Quiz\Livewire;

use App\Tools\Quiz\Models\Attempt;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\QuizPreference;
use App\Tools\Quiz\Models\QuizSession;
use App\Tools\Quiz\Models\Tip;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Dashboard'])]
class QuizDashboard extends Component
{
    /** How far each mastery state counts toward a category's "mastery" percentage. */
    private const array MASTERY_WEIGHT = [
        'new' => 0,
        'learning' => 33,
        'review' => 66,
        'mastered' => 100,
    ];

    public function render()
    {
        $pref = QuizPreference::current();
        $session = QuizSession::today($pref->daily_quota);
        $totalAttempts = Attempt::count();
        $correctAttempts = Attempt::where('correct', true)->count();

        return view('tools.quiz.dashboard', [
            'quota' => $session->quota,
            'completedCount' => min($session->completed_count, $session->quota),
            'tip' => $this->pickTip($session),
            'streak' => $this->currentStreak($pref),
            'poolCount' => Question::count(),
            'avgRecall' => $totalAttempts > 0 ? (int) round($correctAttempts / $totalAttempts * 100) : 0,
            'categories' => $this->categoryBreakdown(),
        ]);
    }

    /**
     * Consecutive days, counting back from today, whose quota was met —
     * broken by any gap day and reset by "Reset Streak" via `streak_broken_at`.
     */
    private function currentStreak(QuizPreference $pref): int
    {
        $cutoff = $pref->streak_broken_at;

        $metDates = QuizSession::query()
            ->whereColumn('completed_count', '>=', 'quota')
            ->where('quota', '>', 0)
            ->when($cutoff, fn ($query) => $query->where('date', '>', $cutoff))
            ->pluck('date')
            ->map(fn ($date) => $date->toDateString())
            ->flip();

        $streak = 0;
        $cursor = today();

        // Today doesn't have to be met yet for the streak to still be "alive" —
        // only yesterday onward has to be unbroken.
        if (! $metDates->has($cursor->toDateString())) {
            $cursor = $cursor->subDay();
        }

        while ($metDates->has($cursor->toDateString())) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    /** @return array<int, array{name: string, count: int, pct: int}> */
    private function categoryBreakdown(): array
    {
        return Category::query()
            ->withCount('questions')
            ->with('questions:id,category_id,mastery_state')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'count' => $category->questions_count,
                'pct' => $category->questions_count > 0
                    ? (int) round($category->questions->avg(fn (Question $q) => self::MASTERY_WEIGHT[$q->mastery_state->value]))
                    : 0,
            ])
            ->all();
    }

    /**
     * Prefers a tip from a category touched by today's session (via its
     * Attempts); falls back to any tip so Dashboard still has something to
     * show before the first answer of the day.
     */
    private function pickTip(QuizSession $session): ?Tip
    {
        $categoryIds = $session->attempts()
            ->with('question')
            ->get()
            ->pluck('question.category_id')
            ->filter()
            ->unique();

        return Tip::query()
            ->when($categoryIds->isNotEmpty(), fn ($query) => $query->whereIn('category_id', $categoryIds))
            ->inRandomOrder()
            ->first()
            ?? Tip::query()->inRandomOrder()->first();
    }
}
