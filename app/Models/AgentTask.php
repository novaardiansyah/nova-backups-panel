<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'agent_id', 'backup_run_id', 'task_type', 'status', 'priority', 'attempts', 'max_attempts', 'lease_token', 'lease_expires_at', 'started_at', 'completed_at'])]
class AgentTask extends Model
{
  public $timestamps = false;

  protected function casts(): array
  {
    return [
      'priority' => 'integer',
      'attempts' => 'integer',
      'max_attempts' => 'integer',
      'lease_expires_at' => 'datetime',
      'started_at' => 'datetime',
      'completed_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<Agent, $this> */
  public function agent(): BelongsTo
  {
    return $this->belongsTo(Agent::class);
  }

  /** @return BelongsTo<BackupRun, $this> */
  public function backupRun(): BelongsTo
  {
    return $this->belongsTo(BackupRun::class);
  }

  /** @return HasMany<BackupJobEvent, $this> */
  public function jobEvents(): HasMany
  {
    return $this->hasMany(BackupJobEvent::class);
  }
}
