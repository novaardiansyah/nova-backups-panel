<?php

namespace App\Models;

use App\Models\Concerns\HasUid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'provider_id', 'uid', 'name', 'slug', 'keys', 'active'])]
class BackupStorage extends Model
{
    use HasUid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'keys' => 'array',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(BackupStorageProvider::class, 'provider_id');
    }
}
