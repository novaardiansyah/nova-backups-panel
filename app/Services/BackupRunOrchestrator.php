<?php

namespace App\Services;

use App\Jobs\PublishOutboxMessagesJob;
use App\Models\AgentTask;
use App\Models\AuditLog;
use App\Models\BackupProfile;
use App\Models\BackupProfileVersion;
use App\Models\BackupRun;
use App\Models\BackupRunAttempt;
use App\Models\BackupRunDestination;
use App\Models\OutboxMessage;
use Illuminate\Support\Facades\DB;

class BackupRunOrchestrator
{
  public function dispatch(BackupProfile $profile, string $triggerType = 'manual', ?int $userId = null, ?string $ipAddress = null): BackupRun
  {
    $profile->loadMissing(['versions', 'destinations']);

    /** @var BackupProfileVersion|null $latestVersion */
    $latestVersion = $profile->versions->sortByDesc('version')->first();

    if ($latestVersion === null) {
      throw new \RuntimeException("Backup profile [{$profile->uid}] has no version configuration.");
    }

    /** @var BackupRun $run */
    $run = DB::transaction(function () use ($profile, $latestVersion, $triggerType, $userId, $ipAddress): BackupRun {
      $run = BackupRun::create([
        'backup_profile_id' => $profile->id,
        'profile_version_id' => $latestVersion->id,
        'agent_id' => $profile->agent_id,
        'trigger_type' => $triggerType,
        'status' => 'pending',
      ]);

      foreach ($profile->destinations as $destination) {
        BackupRunDestination::create([
          'backup_run_id' => $run->id,
          'storage_account_id' => $destination->storage_account_id,
          'remote_folder_id' => $destination->remote_folder_id,
          'is_required' => $destination->is_required,
        ]);
      }

      BackupRunAttempt::create([
        'backup_run_id' => $run->id,
        'attempt_number' => 1,
        'status' => 'pending',
      ]);

      AgentTask::create([
        'agent_id' => $profile->agent_id,
        'backup_run_id' => $run->id,
        'task_type' => 'create_archive',
        'status' => 'pending',
        'priority' => 5,
        'attempts' => 0,
        'max_attempts' => 3,
      ]);

      OutboxMessage::create([
        'event_type' => 'backup_run.created',
        'aggregate_type' => 'BackupRun',
        'aggregate_id' => $run->id,
        'payload' => [
          'backup_run_uid' => $run->uid,
          'backup_profile_uid' => $profile->uid,
          'trigger_type' => $triggerType,
        ],
        'status' => 'pending',
      ]);

      if ($userId !== null) {
        AuditLog::create([
          'user_id' => $userId,
          'action' => 'backup_run.dispatched',
          'subject_type' => 'BackupRun',
          'subject_id' => $run->id,
          'ip_address' => $ipAddress,
          'metadata' => ['profile_name' => $profile->name, 'trigger_type' => $triggerType],
        ]);
      }

      return $run;
    });

    PublishOutboxMessagesJob::dispatch();

    return $run;
  }
}
