<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'agent_task_id', 'event_type', 'message', 'metadata'])]
class BackupJobEvent extends Model
{
  public const ?string UPDATED_AT = null;

  protected function casts(): array
  {
    return [
      'metadata' => 'array',
      'created_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<AgentTask, $this> */
  public function agentTask(): BelongsTo
  {
    return $this->belongsTo(AgentTask::class);
  }
}
