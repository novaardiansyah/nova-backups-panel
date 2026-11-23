<?php

namespace App\Models;

use App\Observers\UidObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UidObserver::class])]
#[Fillable(['uid', 'storage_account_id', 'access_token', 'refresh_token', 'token_expires_at'])]
#[Hidden(['access_token', 'refresh_token'])]
class StorageCredential extends Model
{
  public const ?string CREATED_AT = null;

  protected function casts(): array
  {
    return [
      'access_token' => 'encrypted',
      'refresh_token' => 'encrypted',
      'token_expires_at' => 'datetime',
      'updated_at' => 'datetime',
    ];
  }

  /** @return BelongsTo<StorageAccount, $this> */
  public function storageAccount(): BelongsTo
  {
    return $this->belongsTo(StorageAccount::class);
  }
}
