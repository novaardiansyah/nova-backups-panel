<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'user_id', 'action', 'subject_type', 'subject_id', 'ip_address', 'metadata'])]
class AuditLog extends Model
{
  public const ?string UPDATED_AT = null;

  protected function casts(): array
  {
    return [
      'subject_id' => 'integer',
      'metadata' => 'array',
      'created_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<User, $this> */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
