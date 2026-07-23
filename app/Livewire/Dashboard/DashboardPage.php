<?php

namespace App\Livewire\Dashboard;

use App\Models\QuizSession;
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
        ]);
    }
}
