<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_profile_id', 'schedule_type', 'cron_expression', 'timezone', 'next_run_at', 'is_enabled'])]
class BackupSchedule extends Model
{
  public $timestamps = false;

  protected function casts(): array
  {
    return [
      'next_run_at' => 'datetime',
      'is_enabled' => 'boolean',
    ];
  }

  /** @return BelongsTo<BackupProfile, $this> */
  public function backupProfile(): BelongsTo
  {
    return $this->belongsTo(BackupProfile::class);
  }
}
