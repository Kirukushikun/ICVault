<?php

namespace App\Tools\Quiz\Models;

use Database\Factories\Quiz\TipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'body', 'source_question_id'])]
class Tip extends Model
{
    /** @use HasFactory<\Database\Factories\Quiz\TipFactory> */
    use HasFactory;

    protected static function newFactory(): TipFactory
    {
        return TipFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sourceQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'source_question_id');
    }
}
