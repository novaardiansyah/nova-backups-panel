<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_profile_id', 'version', 'archive_format', 'encrypt_archive', 'encrypt_file_names', 'password_ciphertext', 'compression_level', 'keep_local_default', 'configuration'])]
#[Hidden(['password_ciphertext'])]
class BackupProfileVersion extends Model
{
  public const ?string UPDATED_AT = null;

  protected function casts(): array
  {
    return [
      'version' => 'integer',
      'encrypt_archive' => 'boolean',
      'encrypt_file_names' => 'boolean',
      'password_ciphertext' => 'encrypted',
      'compression_level' => 'integer',
      'keep_local_default' => 'boolean',
      'configuration' => 'array',
      'created_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<BackupProfile, $this> */
  public function backupProfile(): BelongsTo
  {
    return $this->belongsTo(BackupProfile::class);
  }

  /** @return HasMany<BackupRun, $this> */
  public function runs(): HasMany
  {
    return $this->hasMany(BackupRun::class, 'profile_version_id');
  }
}
