<?php

namespace App\Tools\Quiz\Models;

use Database\Factories\Quiz\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'color'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class);
    }
}
