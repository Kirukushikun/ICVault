<?php

namespace App\Tools\Lab\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['review_date', 'progress', 'challenge', 'adjustment'])]
class IdpReview extends Model
{
    protected function casts(): array
    {
        return [
            'review_date' => 'date',
        ];
    }
}
