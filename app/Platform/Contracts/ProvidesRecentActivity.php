<?php

namespace App\Platform\Contracts;

use App\Platform\Support\ActivityItem;

/**
 * Optional companion to ProvidesToolSummary: lets a tool contribute rows to
 * the Hub's activity feed. The Hub merges and sorts whatever comes back, so a
 * tool never needs to know that other tools exist.
 */
interface ProvidesRecentActivity
{
    /** @return array<int, ActivityItem> */
    public function recentActivity(int $limit = 5): array;
}
