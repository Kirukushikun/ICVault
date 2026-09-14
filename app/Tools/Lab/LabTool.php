<?php

namespace App\Tools\Lab;

use App\Platform\Contracts\ProvidesToolSummary;

final class LabTool implements ProvidesToolSummary
{
    public function __construct(private readonly ProjectLibrary $projects) {}

    public function summary(): ?string
    {
        $count = $this->projects->count();

        if ($count === 0) {
            return null;
        }

        return $count.' project'.($count === 1 ? '' : 's').' parked';
    }
}
