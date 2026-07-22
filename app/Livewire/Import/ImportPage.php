<?php

namespace App\Livewire\Import;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Import & Logs'])]
class ImportPage extends Component
{
    public function render()
    {
        return view('livewire.import.import-page');
    }
}
