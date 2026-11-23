<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_profile_id', 'name', 'path', 'is_active'])]
class BackupSource extends Model
{
  public $timestamps = false;

  protected function casts(): array
  {
    return [
      'is_active' => 'boolean',
    ];
  }

  /** @return BelongsTo<BackupProfile, $this> */
  public function backupProfile(): BelongsTo
  {
    return $this->belongsTo(BackupProfile::class);
  }

  /** @return HasMany<BackupExclusion, $this> */
  public function exclusions(): HasMany
  {
    return $this->hasMany(BackupExclusion::class);
  }
}
