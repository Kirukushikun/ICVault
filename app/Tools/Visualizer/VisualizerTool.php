<?php

namespace App\Tools\Visualizer;

use App\Platform\Contracts\ProvidesToolSummary;

final class VisualizerTool implements ProvidesToolSummary
{
    public function __construct(private readonly GuideLibrary $guides) {}

    public function summary(): ?string
    {
        $count = $this->guides->count();

        return $count.' guide'.($count === 1 ? '' : 's').' published';
    }
}
