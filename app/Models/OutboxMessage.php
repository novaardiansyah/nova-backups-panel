<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'event_type', 'aggregate_type', 'aggregate_id', 'payload', 'status', 'published_at'])]
class OutboxMessage extends Model
{
  public const ?string UPDATED_AT = null;

  protected function casts(): array
  {
    return [
      'aggregate_id' => 'integer',
      'payload' => 'array',
      'published_at' => 'datetime',
      'created_at' => 'datetime',
    ];
  }
}
