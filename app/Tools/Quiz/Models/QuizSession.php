<?php

namespace App\Tools\Quiz\Models;

use Database\Factories\Quiz\QuizSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['date', 'quota', 'completed_count'])]
class QuizSession extends Model
{
    /** @use HasFactory<\Database\Factories\Quiz\QuizSessionFactory> */
    use HasFactory;

    protected static function newFactory(): QuizSessionFactory
    {
        return QuizSessionFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quota' => 'integer',
            'completed_count' => 'integer',
        ];
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    /**
     * The single session for today, creating it with the default quota if it
     * doesn't exist yet — mirrors the mockup's "4 / 8 answered" quota card.
     */
    public static function today(int $defaultQuota = 8): self
    {
        // Not firstOrCreate(['date' => ...]) — the `date` cast persists a full
        // `Y-m-d H:i:s` timestamp, so an exact-string match never hits and a new
        // row gets created on every call. whereDate() truncates on both sides.
        return static::whereDate('date', today())->first()
            ?? static::create(['date' => today(), 'quota' => $defaultQuota, 'completed_count' => 0]);
    }
}
