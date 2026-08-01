<?php

namespace App\Tools\Visualizer\Livewire;

use App\Tools\Visualizer\GuideLibrary;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Renders one hand-authored guide full-bleed. Guides bring their own visual
 * design, so this uses the bare canvas layout rather than the app shell.
 */
#[Layout('layouts.canvas')]
class GuideViewer extends Component
{
    public string $slug = '';

    public function mount(string $guide, GuideLibrary $guides): void
    {
        abort_unless($guides->exists($guide), 404);

        $this->slug = $guide;
    }

    public function render(GuideLibrary $guides)
    {
        $guide = $guides->find($this->slug);

        return view($guides->view($this->slug), ['guide' => $guide])
            ->layoutData([
                'title' => 'ICVault — '.$guide['title'],
                'guideTitle' => $guide['title'],
                'guideAccent' => $guide['accent'],
            ]);
    }
}
