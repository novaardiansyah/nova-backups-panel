<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_run_id', 'storage_account_id', 'remote_folder_id', 'is_required'])]
class BackupRunDestination extends Model
{
  public $timestamps = false;

  protected function casts(): array
  {
    return [
      'is_required' => 'boolean',
    ];
  }

  /** @return BelongsTo<BackupRun, $this> */
  public function backupRun(): BelongsTo
  {
    return $this->belongsTo(BackupRun::class);
  }

  /** @return BelongsTo<StorageAccount, $this> */
  public function storageAccount(): BelongsTo
  {
    return $this->belongsTo(StorageAccount::class);
  }
}
