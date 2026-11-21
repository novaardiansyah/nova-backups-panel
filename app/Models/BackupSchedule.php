<?php

namespace App\Models;

use App\Enums\BackupScheduleIntervalUnit;
use App\Enums\BackupType;
use App\Models\Concerns\HasUid;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'uid',
    'storage_id',
    'server_id',
    'name',
    'type',
    'drivers',
    'database_name',
    'source_path',
    'include',
    'exclude',
    'local_destination_path',
    'r2_destination_path',
    'keep_local_backup',
    'is_sync_cloud',
    'filename_pattern',
    'count_backup',
    'max_count_backup',
    'sum_file_size',
    'interval_value',
    'interval_unit',
    'next_backup_at',
    'last_backup_at',
    'is_enabled',
])]
class BackupSchedule extends Model
{
    use HasUid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'storage_id' => 'integer',
            'server_id' => 'integer',
            'type' => BackupType::class,
            'include' => 'array',
            'exclude' => 'array',
            'keep_local_backup' => 'boolean',
            'is_sync_cloud' => 'boolean',
            'count_backup' => 'integer',
            'max_count_backup' => 'integer',
            'sum_file_size' => 'integer',
            'interval_value' => 'integer',
            'interval_unit' => BackupScheduleIntervalUnit::class,
            'next_backup_at' => 'datetime',
            'last_backup_at' => 'datetime',
            'is_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $schedule): void {
            if (empty($schedule->local_destination_path)) {
                $schedule->local_destination_path = '/www/backup/sysadmin/others';
            }

            if ($schedule->next_backup_at === null && $schedule->is_enabled) {
                $schedule->next_backup_at = $schedule->calculateNextBackupAt();
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

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class, 'schedule_id');
    }

    public function calculateNextBackupAt(?CarbonInterface $baseDate = null): CarbonInterface
    {
        $base = $baseDate ? $baseDate->copy() : now();
        $value = (int) ($this->interval_value ?: 1);
        $unitEnum = $this->interval_unit instanceof BackupScheduleIntervalUnit
            ? $this->interval_unit
            : BackupScheduleIntervalUnit::tryFrom((string) $this->interval_unit);

        return match ($unitEnum) {
            BackupScheduleIntervalUnit::Minutes => $base->addMinutes($value),
            BackupScheduleIntervalUnit::Hours => $base->addHours($value),
            BackupScheduleIntervalUnit::Days => $base->addDays($value),
            BackupScheduleIntervalUnit::Weeks => $base->addWeeks($value),
            BackupScheduleIntervalUnit::Months => $base->addMonths($value),
            default => $base->addDays($value ?: 3),
        };
    }

    public static function parsePattern(?string $pattern): ?string
    {
        if (empty($pattern)) {
            return $pattern;
        }

        return preg_replace_callback('/\{([^}]+)\}/', function ($matches) {
            try {
                return now()->format($matches[1]);
            } catch (\Throwable) {
                return $matches[0];
            }
        }, $pattern);
    }

    public static function generateFilename(?string $pattern, string $extension = '.zip'): string
    {
        if (empty($pattern)) {
            return '-';
        }

        return (static::parsePattern($pattern) ?? '') . $extension;
    }
}
