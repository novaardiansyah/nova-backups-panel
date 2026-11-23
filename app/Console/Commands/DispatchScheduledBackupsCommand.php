<?php

namespace App\Console\Commands;

use App\Models\BackupSchedule;
use App\Services\BackupRunOrchestrator;
use Cron\CronExpression;
use Illuminate\Console\Command;
use Throwable;

class DispatchScheduledBackupsCommand extends Command
{
  protected $signature = 'backup:dispatch-scheduled';

  protected $description = 'Dispatch due backup schedules to agent tasks';

  public function handle(BackupRunOrchestrator $orchestrator): int
  {
    $dueSchedules = BackupSchedule::query()
      ->where('is_enabled', true)
      ->where(function ($query): void {
        $query->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
      })
      ->with('backupProfile')
      ->get();

    $dispatched = 0;

    foreach ($dueSchedules as $schedule) {
      $profile = $schedule->backupProfile;
      if ($profile === null || ! $profile->is_active) {
        continue;
      }

      try {
        if ($schedule->next_run_at !== null && $schedule->next_run_at->lte(now())) {
          $orchestrator->dispatch($profile, 'scheduled');
          $dispatched++;
        }

        $cron = new CronExpression($schedule->cron_expression);
        $nextRun = $cron->getNextRunDate('now', 0, false, $schedule->timezone ?: 'UTC');
        $schedule->update(['next_run_at' => $nextRun]);
      } catch (Throwable $e) {
        report($e);
      }
    }

    $this->info("Dispatched {$dispatched} scheduled backup run(s).");

    return self::SUCCESS;
  }
}
