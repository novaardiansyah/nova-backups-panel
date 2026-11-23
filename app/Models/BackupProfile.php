<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'name', 'description', 'agent_id', 'is_active'])]
class BackupProfile extends Model
{
  public const ?string UPDATED_AT = null;

  protected function casts(): array
  {
    return [
      'is_active' => 'boolean',
      'created_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<Agent, $this> */
  public function agent(): BelongsTo
  {
    return $this->belongsTo(Agent::class);
  }

  /** @return HasMany<BackupProfileVersion, $this> */
  public function versions(): HasMany
  {
    return $this->hasMany(BackupProfileVersion::class);
  }

  /** @return HasMany<BackupSource, $this> */
  public function sources(): HasMany
  {
    return $this->hasMany(BackupSource::class);
  }

  /** @return HasMany<BackupProfileDestination, $this> */
  public function destinations(): HasMany
  {
    return $this->hasMany(BackupProfileDestination::class);
  }

  /** @return HasMany<BackupSchedule, $this> */
  public function schedules(): HasMany
  {
    return $this->hasMany(BackupSchedule::class);
  }

  /** @return HasMany<BackupRun, $this> */
  public function runs(): HasMany
  {
    return $this->hasMany(BackupRun::class);
  }
}
