<?php

namespace App\Tools\Quiz\Models;

use App\Tools\Quiz\Enums\ImportStatus;
use Database\Factories\Quiz\ImportBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['source_type', 'category_id', 'raw_content', 'candidates_json', 'status', 'error_message'])]
class ImportBatch extends Model
{
    /** @use HasFactory<\Database\Factories\Quiz\ImportBatchFactory> */
    use HasFactory;

    protected static function newFactory(): ImportBatchFactory
    {
        return ImportBatchFactory::new();
    }

    protected function casts(): array
    {
        return [
            'status' => ImportStatus::class,
            'candidates_json' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
