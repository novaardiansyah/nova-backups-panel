<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'backup_source_id', 'pattern', 'pattern_type'])]
class BackupExclusion extends Model
{
  public $timestamps = false;

  /** @return BelongsTo<BackupSource, $this> */
  public function backupSource(): BelongsTo
  {
    return $this->belongsTo(BackupSource::class);
  }
}
