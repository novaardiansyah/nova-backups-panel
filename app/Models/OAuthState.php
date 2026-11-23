<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'state', 'purpose', 'user_id', 'expires_at'])]
class OAuthState extends Model
{
  public const ?string UPDATED_AT = null;

  protected $table = 'oauth_states';

  protected function casts(): array
  {
    return [
      'expires_at' => 'datetime',
      'created_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<User, $this> */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
