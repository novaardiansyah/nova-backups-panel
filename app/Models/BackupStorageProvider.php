<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'uid', 'name', 'slug', 'keys'])]
class BackupStorageProvider extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'keys' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $provider): void {
            if (empty($provider->uid)) {
                $provider->uid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function storages(): HasMany
    {
        return $this->hasMany(BackupStorage::class, 'provider_id');
    }
}
