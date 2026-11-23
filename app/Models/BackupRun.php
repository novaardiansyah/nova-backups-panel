<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_profile_id', 'profile_version_id', 'agent_id', 'trigger_type', 'status', 'started_at', 'completed_at', 'error_message'])]
class BackupRun extends Model
{
  public $timestamps = false;

  protected function casts(): array
  {
    return [
      'started_at' => 'datetime',
      'completed_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<BackupProfile, $this> */
  public function backupProfile(): BelongsTo
  {
    return $this->belongsTo(BackupProfile::class);
  }

  /** @return BelongsTo<BackupProfileVersion, $this> */
  public function profileVersion(): BelongsTo
  {
    return $this->belongsTo(BackupProfileVersion::class, 'profile_version_id');
  }

  /** @return BelongsTo<Agent, $this> */
  public function agent(): BelongsTo
  {
    return $this->belongsTo(Agent::class);
  }

  /** @return HasMany<BackupRunDestination, $this> */
  public function destinations(): HasMany
  {
    return $this->hasMany(BackupRunDestination::class);
  }

  /** @return HasMany<BackupRunAttempt, $this> */
  public function attempts(): HasMany
  {
    return $this->hasMany(BackupRunAttempt::class);
  }

  /** @return HasMany<BackupArtifact, $this> */
  public function artifacts(): HasMany
  {
    return $this->hasMany(BackupArtifact::class);
  }

  /** @return HasMany<AgentTask, $this> */
  public function tasks(): HasMany
  {
    return $this->hasMany(AgentTask::class);
  }
}
