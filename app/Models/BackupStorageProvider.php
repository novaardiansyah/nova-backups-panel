<?php

namespace App\Models;

use App\Models\Concerns\HasUid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'uid', 'name', 'slug', 'keys'])]
class BackupStorageProvider extends Model
{
    use HasUid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'keys' => 'array',
        ];
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
