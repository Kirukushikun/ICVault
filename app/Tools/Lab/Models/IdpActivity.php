<?php

namespace App\Tools\Lab\Models;

use App\Tools\Lab\Enums\IdpActivityStatus;
use App\Tools\Lab\Enums\IdpActivityType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'area', 'objective', 'description', 'type', 'status', 'target_date', 'notes'])]
class IdpActivity extends Model
{
    /** Id is the activity's stable seed index (0-29), not auto-incrementing. */
    public $incrementing = false;

    protected $keyType = 'int';

    protected function casts(): array
    {
        return [
            'type' => IdpActivityType::class,
            'status' => IdpActivityStatus::class,
            'target_date' => 'date',
        ];
    }

    public function sources(): HasMany
    {
        return $this->hasMany(IdpSource::class, 'activity_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(IdpAttachment::class, 'activity_id');
    }
}
