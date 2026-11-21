<?php

namespace App\Models;

use App\Enums\BackupStatus;
use App\Enums\BackupType;
use App\Models\Concerns\HasUid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'storage_id',
    'server_id',
    'schedule_id',
    'uid',
    'file_name',
    'file_path',
    'cloud_file_path',
    'file_size',
    'checksum',
    'type',
    'started_at',
    'completed_at',
    'duration',
    'status',
    'message',
])]
class Backup extends Model
{
    use HasUid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'storage_id' => 'integer',
            'server_id' => 'integer',
            'schedule_id' => 'integer',
            'file_size' => 'integer',
            'type' => BackupType::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'duration' => 'integer',
            'status' => BackupStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $backup): void {
            if ($backup->started_at === null) {
                $backup->started_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(BackupStorage::class, 'storage_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(BackupStorage::class, 'server_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(BackupSchedule::class, 'schedule_id');
    }
}
