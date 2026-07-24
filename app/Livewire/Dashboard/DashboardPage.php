<?php

namespace App\Livewire\Dashboard;

use App\Models\QuizSession;
use App\Models\Tip;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Dashboard'])]
class DashboardPage extends Component
{
    public function render()
    {
        $session = QuizSession::today();

        return view('livewire.dashboard.dashboard-page', [
            'quota' => $session->quota,
            'completedCount' => min($session->completed_count, $session->quota),
            'tip' => $this->pickTip($session),
        ]);
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
