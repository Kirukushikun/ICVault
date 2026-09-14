<?php

namespace App\Tools\Lab\Livewire;

use App\Tools\Lab\ProjectLibrary;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Renders one lab project full-bleed. Projects bring their own markup — and
 * usually their own inline script driving localStorage — so this uses the
 * bare canvas layout rather than the app shell.
 */
#[Layout('layouts.canvas')]
class ProjectViewer extends Component
{
    public string $slug = '';

    public function mount(string $project, ProjectLibrary $projects): void
    {
        abort_unless($projects->exists($project), 404);

        $this->slug = $project;
    }

    public function render(ProjectLibrary $projects)
    {
        $project = $projects->find($this->slug);

        return view($projects->view($this->slug), ['project' => $project])
            ->layoutData([
                'title' => 'ICVault — '.$project['title'],
                'crumbLabel' => 'Lab Vault',
                'crumbRoute' => 'lab.index',
                'backLabel' => 'Projects',
                'itemTitle' => $project['title'],
                'itemAccent' => $project['accent'],
            ]);
    }
}
