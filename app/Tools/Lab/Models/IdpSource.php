<?php

namespace App\Tools\Lab\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['activity_id', 'label', 'url'])]
class IdpSource extends Model
{
    public function activity(): BelongsTo
    {
        return $this->belongsTo(IdpActivity::class, 'activity_id');
    }
}
