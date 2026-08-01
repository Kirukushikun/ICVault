<?php

use App\Tools\Quiz\QuizTool;
use App\Tools\Visualizer\VisualizerTool;

/*
|--------------------------------------------------------------------------
| Installed Tools
|--------------------------------------------------------------------------
|
| ICVault is a personal platform: the shell in app/Platform stays fixed and
| each capability lives in its own app/Tools/<Name> folder. This file is the
| seam between them — the sidebar and the Hub render whatever is listed here.
|
| To add a tool:
|   1. Create app/Tools/<Name>/ with its own Livewire, Models, Services.
|   2. Register its routes under a matching prefix in routes/web.php.
|   3. Add an entry below.
|
| Nothing else needs to change. Keep this file pure data so `config:cache`
| keeps working — live numbers come from the `summary` provider class.
|
*/

return [

    'quiz' => [
        'name' => 'Quiz Vault',
        'tagline' => 'Spaced-recall drills across your knowledge pool — multiple choice, fill-in, write-the-code.',
        'icon' => '▣',
        'accent' => '#C3073F',
        'route' => 'quiz.dashboard',
        'route_pattern' => 'quiz.*',
        'settings_route' => 'quiz.settings',
        'status' => 'active',
        'enabled' => true,
        'summary' => QuizTool::class,
    ],

    'visualizer' => [
        'name' => 'Concept Visualizer',
        'tagline' => 'Interactive, step-by-step walkthroughs of a topic — built as hand-authored field guides.',
        'icon' => '◈',
        'accent' => '#2E9CCA',
        'route' => 'visualizer.index',
        'route_pattern' => 'visualizer.*',
        'status' => 'beta',
        'enabled' => true,
        'summary' => VisualizerTool::class,
    ],

];
