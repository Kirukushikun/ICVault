<?php

namespace App\Tools\Quiz\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Single-row settings, same pattern as QuizSession::today() — this is a
 * personal, single-user tool, so there's no accounts table to key preferences
 * off of.
 */
#[Fillable([
    'default_mode', 'daily_quota', 'auto_reveal', 'shuffle_order',
    'show_difficulty', 'timed_mode', 'streak_reminder', 'weekly_summary',
    'new_questions_notif', 'streak_broken_at',
])]
class QuizPreference extends Model
{
    /**
     * Mirrors the migration's column defaults — `create([])` only returns
     * what's already in memory, and Eloquent doesn't re-fetch DB-side
     * defaults after an insert, so leaving this out would hand back a model
     * full of nulls even though the row itself is filled in correctly.
     */
    protected $attributes = [
        'default_mode' => 'shuffle',
        'daily_quota' => 8,
        'auto_reveal' => true,
        'shuffle_order' => true,
        'show_difficulty' => true,
        'timed_mode' => false,
        'streak_reminder' => true,
        'weekly_summary' => true,
        'new_questions_notif' => false,
    ];

    protected function casts(): array
    {
        return [
            'daily_quota' => 'integer',
            'auto_reveal' => 'boolean',
            'shuffle_order' => 'boolean',
            'show_difficulty' => 'boolean',
            'timed_mode' => 'boolean',
            'streak_reminder' => 'boolean',
            'weekly_summary' => 'boolean',
            'new_questions_notif' => 'boolean',
            'streak_broken_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        return static::query()->first() ?? static::create([]);
    }
}
