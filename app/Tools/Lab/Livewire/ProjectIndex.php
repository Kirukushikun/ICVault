<?php

namespace App\Tools\Lab\Livewire;

use App\Tools\Lab\ProjectLibrary;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Lab Vault'])]
class ProjectIndex extends Component
{
    public function render(ProjectLibrary $projects)
    {
        return view('tools.lab.index', [
            'projects' => $projects->all(),
        ]);
    }
}
