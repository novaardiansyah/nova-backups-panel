<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_run_id', 'attempt_number', 'status', 'started_at', 'completed_at', 'error_message'])]
class BackupRunAttempt extends Model
{
  public $timestamps = false;

  protected function casts(): array
  {
    return [
      'attempt_number' => 'integer',
      'started_at' => 'datetime',
      'completed_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<BackupRun, $this> */
  public function backupRun(): BelongsTo
  {
    return $this->belongsTo(BackupRun::class);
  }
}
