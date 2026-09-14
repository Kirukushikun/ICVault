<?php

namespace App\Tools\Lab\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['activity_id', 'disk', 'path', 'original_name', 'size'])]
class IdpAttachment extends Model
{
    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(IdpActivity::class, 'activity_id');
    }

    public function isImage(): bool
    {
        $extension = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));

        return in_array($extension, self::IMAGE_EXTENSIONS, true);
    }
}
