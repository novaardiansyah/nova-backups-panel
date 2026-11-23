<?php

namespace App\Jobs;

use App\Models\OutboxMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PublishOutboxMessagesJob implements ShouldQueue
{
  use Queueable;

  public function handle(): void
  {
    OutboxMessage::where('status', 'pending')
      ->orderBy('id')
      ->limit(100)
      ->update([
        'status' => 'published',
        'published_at' => now(),
      ]);
  }
}
