<?php

namespace App\Tools\Lab\Services;

use App\Tools\Lab\Enums\IdpActivityStatus;
use App\Tools\Lab\IdpSeedData;
use App\Tools\Lab\Models\IdpActivity;
use App\Tools\Lab\Models\IdpSnapshot;
use Illuminate\Support\Carbon;

/**
 * Owns the one number the whole dashboard is built from — overall % — and
 * the daily snapshot that turns it into the progress-over-time chart.
 */
final class IdpTrackerService
{
    /** Effort weight per activity type, for a nicer overall-% feel. */
    private const WEIGHT = ['70' => 0.5, '20' => 0.3, '10' => 0.2];

    public function computeOverall(): int
    {
        $num = 0.0;
        $den = 0.0;

        foreach (IdpActivity::all() as $activity) {
            $weight = self::WEIGHT[$activity->type->value];
            $num += $this->progressOf($activity->status) * $weight;
            $den += $weight;
        }

        return $den > 0 ? (int) round($num / $den * 100) : 0;
    }

    public function progressOf(IdpActivityStatus $status): float
    {
        return match ($status) {
            IdpActivityStatus::Completed => 1.0,
            IdpActivityStatus::InProgress => 0.5,
            default => 0.0,
        };
    }

    /**
     * Upserts today's point on the progress-over-time chart from the current
     * overall %. Looked up with whereDate() rather than updateOrCreate()'s
     * plain equality match, since the `date` cast round-trips through a
     * full datetime string — a literal string compare against today's plain
     * "Y-m-d" would never find the row it just created.
     */
    public function recordSnapshot(): void
    {
        $today = Carbon::today()->toDateString();
        $pct = $this->computeOverall();

        $snapshot = IdpSnapshot::whereDate('snapshot_date', $today)->first();

        if ($snapshot) {
            $snapshot->update(['overall_pct' => $pct]);
        } else {
            IdpSnapshot::create(['snapshot_date' => $today, 'overall_pct' => $pct]);
        }
    }

    /** Puts every activity back to its seeded status/notes/target — sources, attachments, and reviews are untouched. */
    public function reset(): void
    {
        IdpSeedData::apply();
        $this->recordSnapshot();
    }
}
