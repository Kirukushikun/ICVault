<?php

namespace App\Tools\Quiz\Models;

use Database\Factories\Quiz\AttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['question_id', 'quiz_session_id', 'correct', 'answered_at'])]
class Attempt extends Model
{
    /** @use HasFactory<\Database\Factories\Quiz\AttemptFactory> */
    use HasFactory;

    protected static function newFactory(): AttemptFactory
    {
        return AttemptFactory::new();
    }

    protected function casts(): array
    {
        return [
            'correct' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function quizSession(): BelongsTo
    {
        return $this->belongsTo(QuizSession::class);
    }
}
