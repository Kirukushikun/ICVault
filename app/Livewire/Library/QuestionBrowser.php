<?php

namespace App\Livewire\Library;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Library'])]
class QuestionBrowser extends Component
{
    public function render()
    {
        return view('livewire.library.question-browser');
    }
}
