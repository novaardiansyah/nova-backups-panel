<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'name', 'hostname', 'status', 'agent_version', 'last_seen_at'])]
class Agent extends Model
{
  public const ?string UPDATED_AT = null;

  protected function casts(): array
  {
    return [
      'last_seen_at' => 'datetime',
      'created_at' => 'datetime',
    ];
  }

  /** @return HasMany<AgentCredential, $this> */
  public function credentials(): HasMany
  {
    return $this->hasMany(AgentCredential::class);
  }

  /** @return HasMany<BackupProfile, $this> */
  public function backupProfiles(): HasMany
  {
    return $this->hasMany(BackupProfile::class);
  }

  /** @return HasMany<BackupRun, $this> */
  public function backupRuns(): HasMany
  {
    return $this->hasMany(BackupRun::class);
  }

  /** @return HasMany<AgentTask, $this> */
  public function tasks(): HasMany
  {
    return $this->hasMany(AgentTask::class);
  }

  /** @return HasMany<AgentEvent, $this> */
  public function events(): HasMany
  {
    return $this->hasMany(AgentEvent::class);
  }
}
