<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'name', 'provider', 'account_email', 'status'])]
class StorageAccount extends Model
{
  public const ?string UPDATED_AT = null;

  protected function casts(): array
  {
    return [
      'created_at' => 'datetime',
    ];
  }

  /** @return HasOne<StorageCredential, $this> */
  public function credential(): HasOne
  {
    return $this->hasOne(StorageCredential::class);
  }

  /** @return HasMany<BackupProfileDestination, $this> */
  public function profileDestinations(): HasMany
  {
    return $this->hasMany(BackupProfileDestination::class);
  }

  /** @return HasMany<BackupRunDestination, $this> */
  public function runDestinations(): HasMany
  {
    return $this->hasMany(BackupRunDestination::class);
  }

  /** @return HasMany<BackupUpload, $this> */
  public function uploads(): HasMany
  {
    return $this->hasMany(BackupUpload::class);
  }
}
