<?php

namespace App\Livewire\Quiz;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Quiz Session'])]
class QuizSession extends Component
{
    public function render()
    {
        return view('livewire.quiz.quiz-session');
    }
}
