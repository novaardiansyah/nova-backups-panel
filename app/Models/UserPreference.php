<?php

namespace App\Models;

use App\Models\Concerns\HasUid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'uid', 'theme', 'locale'])]
class UserPreference extends Model
{
    use HasUid;
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
