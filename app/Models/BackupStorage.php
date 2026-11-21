<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'provider_id', 'uid', 'name', 'slug', 'keys', 'active'])]
class BackupStorage extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'keys' => 'array',
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $storage): void {
            if (empty($storage->uid)) {
                $storage->uid = (string) Str::uuid();
            }
        });
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
