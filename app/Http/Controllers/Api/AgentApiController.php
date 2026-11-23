<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentEvent;
use App\Models\AgentTask;
use App\Models\BackupArtifact;
use App\Models\BackupJobEvent;
use App\Models\BackupUpload;
use App\Models\OutboxMessage;
use App\Models\StorageAccount;
use App\Services\GoogleDriveTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AgentApiController extends Controller
{
  public function heartbeat(Request $request): JsonResponse
  {
    /** @var Agent $agent */
    $agent = $request->attributes->get('agent');

    $validated = $request->validate([
      'hostname' => ['nullable', 'string', 'max:255'],
      'agent_version' => ['nullable', 'string', 'max:30'],
      'status' => ['nullable', 'string', 'in:online,busy,idle'],
      'metadata' => ['nullable', 'array'],
    ]);

    $agent->forceFill([
      'hostname' => $validated['hostname'] ?? $agent->hostname,
      'agent_version' => $validated['agent_version'] ?? $agent->agent_version,
      'status' => $validated['status'] ?? 'online',
      'last_seen_at' => now(),
    ])->save();

    AgentEvent::create([
      'agent_id' => $agent->id,
      'event_type' => 'heartbeat',
      'message' => 'Agent heartbeat received.',
      'metadata' => $validated['metadata'] ?? null,
    ]);

    return response()->json([
      'ok' => true,
      'agent_uid' => $agent->uid,
      'server_time' => now()->toIso8601String(),
    ]);
  }

  public function claimTask(Request $request, GoogleDriveTokenService $tokenService): JsonResponse
  {
    /** @var Agent $agent */
    $agent = $request->attributes->get('agent');

    $task = DB::transaction(function () use ($agent): ?AgentTask {
      $candidate = AgentTask::query()
        ->where('agent_id', $agent->id)
        ->where(function ($query): void {
          $query->where('status', 'pending')
            ->orWhere(function ($q): void {
              $q->where('status', 'leased')->where('lease_expires_at', '<', now());
            });
        })
        ->orderByDesc('priority')
        ->orderBy('id')
        ->lockForUpdate()
        ->first();

      if ($candidate === null) {
        return null;
      }

      $candidate->forceFill([
        'status' => 'leased',
        'lease_token' => (string) Str::uuid7(),
        'lease_expires_at' => now()->addMinutes(15),
        'attempts' => $candidate->attempts + 1,
        'started_at' => $candidate->started_at ?? now(),
      ])->save();

      $run = $candidate->backupRun;
      if ($run !== null) {
        $run->forceFill([
          'status' => 'running',
          'started_at' => $run->started_at ?? now(),
        ])->save();

        $run->attempts()
          ->where('attempt_number', $candidate->attempts)
          ->update(['status' => 'running', 'started_at' => now()]);
      }

      return $candidate;
    });

    if ($task === null) {
      return response()->json(['ok' => true, 'task' => null]);
    }

    $task->loadMissing([
      'backupRun.backupProfile.sources.exclusions',
      'backupRun.profileVersion',
      'backupRun.destinations.storageAccount.credential',
    ]);

    $run = $task->backupRun;
    $version = $run?->profileVersion;
    $profile = $run?->backupProfile;

    $destinations = [];
    if ($run !== null) {
      foreach ($run->destinations as $dest) {
        $account = $dest->storageAccount;
        if ($account === null) {
          continue;
        }

        $accessToken = null;
        try {
          $accessToken = $tokenService->getValidAccessToken($account);
        } catch (Throwable) {
          $accessToken = null;
        }

        $destinations[] = [
          'storage_account_uid' => $account->uid,
          'provider' => $account->provider,
          'account_email' => $account->account_email,
          'remote_folder_id' => $dest->remote_folder_id,
          'is_required' => $dest->is_required,
          'access_token' => $accessToken,
        ];
      }
    }

    $sources = [];
    if ($profile !== null) {
      foreach ($profile->sources->where('is_active', true) as $source) {
        $sources[] = [
          'uid' => $source->uid,
          'name' => $source->name,
          'path' => $source->path,
          'exclusions' => $source->exclusions->map(fn ($ex) => [
            'pattern' => $ex->pattern,
            'pattern_type' => $ex->pattern_type,
          ])->values()->all(),
        ];
      }
    }

    BackupJobEvent::create([
      'agent_task_id' => $task->id,
      'event_type' => 'task_claimed',
      'message' => "Task claimed by agent {$agent->name} (attempt {$task->attempts}).",
    ]);

    return response()->json([
      'ok' => true,
      'task' => [
        'uid' => $task->uid,
        'lease_token' => $task->lease_token,
        'task_type' => $task->task_type,
        'attempts' => $task->attempts,
        'max_attempts' => $task->max_attempts,
        'backup_run_uid' => $run?->uid,
        'profile_name' => $profile?->name,
        'profile_version' => $version !== null ? [
          'version' => $version->version,
          'archive_format' => $version->archive_format,
          'encrypt_archive' => $version->encrypt_archive,
          'encrypt_file_names' => $version->encrypt_file_names,
          'rar_password' => $version->password_ciphertext,
          'compression_level' => $version->compression_level,
          'keep_local_default' => $version->keep_local_default,
        ] : null,
        'sources' => $sources,
        'destinations' => $destinations,
      ],
    ]);
  }

  public function reportEvent(Request $request, AgentTask $task): JsonResponse
  {
    if (! $this->verifyLease($request, $task)) {
      return response()->json(['ok' => false, 'message' => 'Invalid lease token or agent.'], 403);
    }

    $validated = $request->validate([
      'event_type' => ['required', 'string', 'max:50'],
      'message' => ['nullable', 'string'],
      'metadata' => ['nullable', 'array'],
    ]);

    BackupJobEvent::create([
      'agent_task_id' => $task->id,
      'event_type' => $validated['event_type'],
      'message' => $validated['message'] ?? null,
      'metadata' => $validated['metadata'] ?? null,
    ]);

    return response()->json(['ok' => true]);
  }

  public function completeTask(Request $request, AgentTask $task): JsonResponse
  {
    if (! $this->verifyLease($request, $task)) {
      return response()->json(['ok' => false, 'message' => 'Invalid lease token or agent.'], 403);
    }

    $validated = $request->validate([
      'artifact' => ['required', 'array'],
      'artifact.filename' => ['required', 'string', 'max:255'],
      'artifact.local_path' => ['required', 'string', 'max:1000'],
      'artifact.file_size' => ['required', 'integer', 'min:0'],
      'artifact.checksum_sha256' => ['required', 'string', 'size:64'],
      'artifact.keep_local' => ['required', 'boolean'],
      'artifact.local_status' => ['required', 'string', 'in:available,deleted'],
      'uploads' => ['nullable', 'array'],
      'uploads.*.storage_account_uid' => ['required', 'string'],
      'uploads.*.status' => ['required', 'string', 'in:completed,failed'],
      'uploads.*.remote_file_id' => ['nullable', 'string', 'max:255'],
      'uploads.*.remote_filename' => ['nullable', 'string', 'max:255'],
      'uploads.*.uploaded_size' => ['nullable', 'integer', 'min:0'],
      'uploads.*.error_message' => ['nullable', 'string'],
    ]);

    DB::transaction(function () use ($task, $validated): void {
      $artifactData = $validated['artifact'];
      $artifact = BackupArtifact::create([
        'backup_run_id' => $task->backup_run_id,
        'filename' => $artifactData['filename'],
        'local_path' => $artifactData['local_path'],
        'file_size' => $artifactData['file_size'],
        'checksum_sha256' => $artifactData['checksum_sha256'],
        'keep_local' => $artifactData['keep_local'],
        'local_status' => $artifactData['local_status'],
      ]);

      $hasFailedRequired = false;
      foreach ($validated['uploads'] ?? [] as $uploadData) {
        $account = StorageAccount::where('uid', $uploadData['storage_account_uid'])->first();
        if ($account === null) {
          continue;
        }

        BackupUpload::create([
          'backup_artifact_id' => $artifact->id,
          'storage_account_id' => $account->id,
          'status' => $uploadData['status'],
          'remote_file_id' => $uploadData['remote_file_id'] ?? null,
          'remote_filename' => $uploadData['remote_filename'] ?? $artifactData['filename'],
          'uploaded_size' => $uploadData['uploaded_size'] ?? $artifactData['file_size'],
          'uploaded_at' => $uploadData['status'] === 'completed' ? now() : null,
          'error_message' => $uploadData['error_message'] ?? null,
        ]);

        if ($uploadData['status'] === 'failed') {
          $hasFailedRequired = true;
        }
      }

      $finalStatus = $hasFailedRequired ? 'failed' : 'completed';

      $task->forceFill([
        'status' => $finalStatus,
        'completed_at' => now(),
      ])->save();

      $run = $task->backupRun;
      if ($run !== null) {
        $run->forceFill([
          'status' => $finalStatus,
          'completed_at' => now(),
          'error_message' => $hasFailedRequired ? 'One or more uploads failed.' : null,
        ])->save();

        $run->attempts()
          ->where('attempt_number', $task->attempts)
          ->update([
            'status' => $finalStatus,
            'completed_at' => now(),
          ]);

        OutboxMessage::create([
          'event_type' => "backup_run.{$finalStatus}",
          'aggregate_type' => 'BackupRun',
          'aggregate_id' => $run->id,
          'payload' => [
            'backup_run_uid' => $run->uid,
            'artifact_uid' => $artifact->uid,
            'status' => $finalStatus,
          ],
          'status' => 'pending',
        ]);
      }

      BackupJobEvent::create([
        'agent_task_id' => $task->id,
        'event_type' => "task_{$finalStatus}",
        'message' => $hasFailedRequired ? 'Task completed with upload errors.' : 'Archive created and uploaded successfully.',
      ]);
    });

    return response()->json(['ok' => true]);
  }

  public function failTask(Request $request, AgentTask $task): JsonResponse
  {
    if (! $this->verifyLease($request, $task)) {
      return response()->json(['ok' => false, 'message' => 'Invalid lease token or agent.'], 403);
    }

    $validated = $request->validate([
      'error_message' => ['required', 'string'],
    ]);

    DB::transaction(function () use ($task, $validated): void {
      $canRetry = $task->attempts < $task->max_attempts;

      if ($canRetry) {
        $task->forceFill([
          'status' => 'pending',
          'lease_token' => null,
          'lease_expires_at' => null,
        ])->save();
      } else {
        $task->forceFill([
          'status' => 'failed',
          'completed_at' => now(),
        ])->save();
      }

      $run = $task->backupRun;
      if ($run !== null) {
        $run->attempts()
          ->where('attempt_number', $task->attempts)
          ->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $validated['error_message'],
          ]);

        if (! $canRetry) {
          $run->forceFill([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $validated['error_message'],
          ])->save();

          OutboxMessage::create([
            'event_type' => 'backup_run.failed',
            'aggregate_type' => 'BackupRun',
            'aggregate_id' => $run->id,
            'payload' => ['backup_run_uid' => $run->uid, 'error' => $validated['error_message']],
            'status' => 'pending',
          ]);
        }
      }

      BackupJobEvent::create([
        'agent_task_id' => $task->id,
        'event_type' => $canRetry ? 'task_retry_scheduled' : 'task_failed',
        'message' => $validated['error_message'],
      ]);
    });

    return response()->json(['ok' => true]);
  }

  private function verifyLease(Request $request, AgentTask $task): bool
  {
    /** @var Agent $agent */
    $agent = $request->attributes->get('agent');
    $leaseToken = (string) ($request->input('lease_token') ?: $request->header('X-Lease-Token', ''));

    return $task->agent_id === $agent->id
      && $task->status === 'leased'
      && $leaseToken !== ''
      && hash_equals((string) $task->lease_token, $leaseToken);
  }
}
