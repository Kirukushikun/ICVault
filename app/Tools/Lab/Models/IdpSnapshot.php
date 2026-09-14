<?php

namespace App\Tools\Lab\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['snapshot_date', 'overall_pct'])]
class IdpSnapshot extends Model
{
    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'overall_pct' => 'integer',
        ];
    }
}
