<?php

namespace App\Platform\Contracts;

/**
 * Lets a tool contribute a one-line status to the Hub card and nothing more.
 * Keeps config/tools.php pure data (so it stays config:cache-safe) while the
 * live numbers stay owned by the tool that understands them.
 */
interface ProvidesToolSummary
{
    /**
     * Short status line, e.g. "184 cards · 12 day streak".
     * Return null when the tool has nothing worth showing yet.
     */
    public function summary(): ?string;
}
