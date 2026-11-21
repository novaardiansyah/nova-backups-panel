<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUid
{
    protected static function bootHasUid(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->getAttribute('uid'))) {
                $model->setAttribute('uid', (string) Str::uuid7());
            }
        });
    }
}
