<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_run_id', 'filename', 'local_path', 'file_size', 'checksum_sha256', 'keep_local', 'local_status'])]
class BackupArtifact extends Model
{
  public const ?string UPDATED_AT = null;

  protected function casts(): array
  {
    return [
      'file_size' => 'integer',
      'keep_local' => 'boolean',
      'created_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<BackupRun, $this> */
  public function backupRun(): BelongsTo
  {
    return $this->belongsTo(BackupRun::class);
  }

  /** @return HasMany<BackupUpload, $this> */
  public function uploads(): HasMany
  {
    return $this->hasMany(BackupUpload::class);
  }
}
