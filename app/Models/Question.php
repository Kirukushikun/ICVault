<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\MasteryState;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'import_batch_id',
    'difficulty',
    'type',
    'prompt',
    'options_json',
    'answer',
    'mastery_state',
    'mastery_streak',
    'last_reviewed_at',
])]
class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'difficulty' => Difficulty::class,
            'type' => QuestionType::class,
            'options_json' => 'array',
            'mastery_state' => MasteryState::class,
            'mastery_streak' => 'integer',
            'last_reviewed_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }
}
