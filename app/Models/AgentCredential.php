<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'agent_id', 'token_hash', 'expires_at', 'revoked_at'])]
#[Hidden(['token_hash'])]
class AgentCredential extends Model
{
  public $timestamps = false;

  protected function casts(): array
  {
    return [
      'expires_at' => 'datetime',
      'revoked_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<Agent, $this> */
  public function agent(): BelongsTo
  {
    return $this->belongsTo(Agent::class);
  }
}
