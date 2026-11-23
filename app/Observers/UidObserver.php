<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UidObserver
{
  public function creating(Model $model): void
  {
    if (empty($model->getAttribute('uid'))) {
      $model->setAttribute('uid', (string) Str::uuid7());
    }
  }
}
