<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Dashboard'])]
class DashboardPage extends Component
{
    public function render()
    {
        return view('livewire.dashboard.dashboard-page');
    }
}
