<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_profile_id', 'storage_account_id', 'remote_folder_id', 'is_required'])]
class BackupProfileDestination extends Model
{
  public $timestamps = false;

  protected function casts(): array
  {
    return [
      'is_required' => 'boolean',
    ];
  }

  /** @return BelongsTo<BackupProfile, $this> */
  public function backupProfile(): BelongsTo
  {
    return $this->belongsTo(BackupProfile::class);
  }

  /** @return BelongsTo<StorageAccount, $this> */
  public function storageAccount(): BelongsTo
  {
    return $this->belongsTo(StorageAccount::class);
  }
}
