<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Settings'])]
class SettingsPage extends Component
{
    public function render()
    {
        return view('livewire.settings.settings-page');
    }
}
