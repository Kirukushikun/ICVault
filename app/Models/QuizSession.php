<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['date', 'quota', 'completed_count'])]
class QuizSession extends Model
{
    /** @use HasFactory<\Database\Factories\QuizSessionFactory> */
    use HasFactory;

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
}
