<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_artifact_id', 'storage_account_id', 'status', 'remote_file_id', 'remote_filename', 'uploaded_size', 'uploaded_at', 'error_message'])]
class BackupUpload extends Model
{
  public $timestamps = false;

  protected function casts(): array
  {
    return [
      'uploaded_size' => 'integer',
      'uploaded_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<BackupArtifact, $this> */
  public function backupArtifact(): BelongsTo
  {
    return $this->belongsTo(BackupArtifact::class);
  }

  /** @return BelongsTo<StorageAccount, $this> */
  public function storageAccount(): BelongsTo
  {
    return $this->belongsTo(StorageAccount::class);
  }
}
