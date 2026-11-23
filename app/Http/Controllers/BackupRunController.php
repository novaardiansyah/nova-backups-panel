<?php

namespace App\Http\Controllers;

use App\Models\AgentTask;
use App\Models\AuditLog;
use App\Models\BackupProfile;
use App\Models\BackupRun;
use App\Models\BackupRunAttempt;
use App\Models\User;
use App\Services\BackupRunOrchestrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupRunController extends Controller
{
  public function store(Request $request, BackupProfile $backupProfile, BackupRunOrchestrator $orchestrator): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    $orchestrator->dispatch($backupProfile, 'manual', $user->id, $request->ip());

    return back()->with('status', "Backup run queued for {$backupProfile->name}.");
  }

  public function retry(Request $request, BackupRun $backupRun): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    if ($backupRun->status !== 'failed') {
      return back()->withErrors(['run' => 'Only failed backup runs can be retried.']);
    }

    DB::transaction(function () use ($backupRun, $user, $request): void {
      $nextAttempt = ($backupRun->attempts()->max('attempt_number') ?? 0) + 1;

      $backupRun->update([
        'status' => 'pending',
        'error_message' => null,
        'completed_at' => null,
      ]);

      BackupRunAttempt::create([
        'backup_run_id' => $backupRun->id,
        'attempt_number' => $nextAttempt,
        'status' => 'pending',
      ]);

      AgentTask::create([
        'agent_id' => $backupRun->agent_id,
        'backup_run_id' => $backupRun->id,
        'task_type' => 'create_archive',
        'status' => 'pending',
        'priority' => 8,
        'attempts' => 0,
        'max_attempts' => 3,
      ]);

      AuditLog::create([
        'user_id' => $user->id,
        'action' => 'backup_run.retried',
        'subject_type' => 'BackupRun',
        'subject_id' => $backupRun->id,
        'ip_address' => $request->ip(),
        'metadata' => ['attempt_number' => $nextAttempt],
      ]);
    });

    return back()->with('status', 'Backup run retry queued.');
  }
}
