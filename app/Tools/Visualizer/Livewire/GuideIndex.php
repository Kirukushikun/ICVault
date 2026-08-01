<?php

namespace App\Tools\Visualizer\Livewire;

use App\Tools\Visualizer\GuideLibrary;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Concept Visualizer'])]
class GuideIndex extends Component
{
    public function render(GuideLibrary $guides)
    {
        return view('tools.visualizer.index', [
            'guides' => $guides->all(),
        ]);
    }
}
