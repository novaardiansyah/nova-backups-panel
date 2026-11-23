<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\BackupExclusion;
use App\Models\BackupProfile;
use App\Models\BackupProfileDestination;
use App\Models\BackupProfileVersion;
use App\Models\BackupSchedule;
use App\Models\BackupSource;
use App\Models\StorageAccount;
use App\Models\User;
use Cron\CronExpression;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class BackupProfileController extends Controller
{
  public function store(Request $request): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    $validated = $this->validateProfile($request, true);

    $agent = Agent::where('uid', $validated['agent_uid'])->firstOrFail();

    $profile = DB::transaction(function () use ($validated, $agent, $user, $request): BackupProfile {
      $profile = BackupProfile::create([
        'name' => trim($validated['name']),
        'description' => $validated['description'] ?? null,
        'agent_id' => $agent->id,
        'is_active' => (bool) ($validated['is_active'] ?? true),
      ]);

      $this->createVersion($profile, 1, $validated, null);
      $this->syncSourcesAndDestinations($profile, $validated);
      $this->syncSchedule($profile, $validated);

      AuditLog::create([
        'user_id' => $user->id,
        'action' => 'backup_profile.created',
        'subject_type' => 'BackupProfile',
        'subject_id' => $profile->id,
        'ip_address' => $request->ip(),
        'metadata' => ['name' => $profile->name],
      ]);

      return $profile;
    });

    return back()->with('status', "Backup profile {$profile->name} created.");
  }

  public function update(Request $request, BackupProfile $backupProfile): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    $validated = $this->validateProfile($request, false);
    $agent = Agent::where('uid', $validated['agent_uid'])->firstOrFail();

    DB::transaction(function () use ($backupProfile, $validated, $agent, $user, $request): void {
      $backupProfile->update([
        'name' => trim($validated['name']),
        'description' => $validated['description'] ?? null,
        'agent_id' => $agent->id,
        'is_active' => (bool) ($validated['is_active'] ?? true),
      ]);

      /** @var BackupProfileVersion|null $latest */
      $latest = $backupProfile->versions()->orderByDesc('version')->first();
      $nextVersion = ($latest?->version ?? 0) + 1;

      $this->createVersion($backupProfile, $nextVersion, $validated, $latest?->password_ciphertext);
      $this->syncSourcesAndDestinations($backupProfile, $validated);
      $this->syncSchedule($backupProfile, $validated);

      AuditLog::create([
        'user_id' => $user->id,
        'action' => 'backup_profile.updated',
        'subject_type' => 'BackupProfile',
        'subject_id' => $backupProfile->id,
        'ip_address' => $request->ip(),
        'metadata' => ['name' => $backupProfile->name, 'version' => $nextVersion],
      ]);
    });

    return back()->with('status', "Backup profile {$backupProfile->name} updated.");
  }

  public function destroy(Request $request, BackupProfile $backupProfile): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    if ($backupProfile->runs()->exists()) {
      $backupProfile->update(['is_active' => false]);

      AuditLog::create([
        'user_id' => $user->id,
        'action' => 'backup_profile.deactivated',
        'subject_type' => 'BackupProfile',
        'subject_id' => $backupProfile->id,
        'ip_address' => $request->ip(),
      ]);

      return back()->with('status', "Backup profile {$backupProfile->name} has run history and was deactivated.");
    }

    $name = $backupProfile->name;
    $id = $backupProfile->id;
    $backupProfile->delete();

    AuditLog::create([
      'user_id' => $user->id,
      'action' => 'backup_profile.deleted',
      'subject_type' => 'BackupProfile',
      'subject_id' => $id,
      'ip_address' => $request->ip(),
      'metadata' => ['name' => $name],
    ]);

    return back()->with('status', "Backup profile {$name} deleted.");
  }

  /** @return array<string, mixed> */
  private function validateProfile(Request $request, bool $isCreating): array
  {
    return $request->validate([
      'name' => ['required', 'string', 'max:150'],
      'description' => ['nullable', 'string'],
      'agent_uid' => ['required', 'string', 'exists:agents,uid'],
      'is_active' => ['boolean'],
      'encrypt_archive' => ['boolean'],
      'encrypt_file_names' => ['boolean'],
      'rar_password' => [$isCreating ? 'required_if:encrypt_archive,true' : 'nullable', 'nullable', 'string', 'min:4'],
      'compression_level' => ['integer', 'min:0', 'max:5'],
      'keep_local_default' => ['boolean'],
      'sources' => ['required', 'array', 'min:1'],
      'sources.*.name' => ['required', 'string', 'max:150'],
      'sources.*.path' => ['required', 'string', 'max:1000'],
      'sources.*.exclusions' => ['nullable', 'array'],
      'sources.*.exclusions.*' => ['string', 'max:1000'],
      'destinations' => ['nullable', 'array'],
      'destinations.*.storage_account_uid' => ['required', 'string', 'exists:storage_accounts,uid'],
      'destinations.*.remote_folder_id' => ['required', 'string', 'max:255'],
      'destinations.*.is_required' => ['boolean'],
      'schedule_enabled' => ['boolean'],
      'schedule_type' => ['nullable', 'string', 'in:hourly,daily,weekly,custom'],
      'cron_expression' => ['nullable', 'string', 'max:100'],
      'timezone' => ['nullable', 'string', 'max:100'],
    ]);
  }

  /** @param array<string, mixed> $validated */
  private function createVersion(BackupProfile $profile, int $version, array $validated, ?string $fallbackPassword): void
  {
    $encryptArchive = (bool) ($validated['encrypt_archive'] ?? true);
    $rawPassword = $validated['rar_password'] ?? null;
    $password = is_string($rawPassword) && $rawPassword !== '' ? $rawPassword : $fallbackPassword;

    BackupProfileVersion::create([
      'backup_profile_id' => $profile->id,
      'version' => $version,
      'archive_format' => 'rar',
      'encrypt_archive' => $encryptArchive,
      'encrypt_file_names' => (bool) ($validated['encrypt_file_names'] ?? true),
      'password_ciphertext' => $encryptArchive ? $password : null,
      'compression_level' => (int) ($validated['compression_level'] ?? 5),
      'keep_local_default' => (bool) ($validated['keep_local_default'] ?? false),
      'configuration' => [
        'sources' => $validated['sources'],
        'destinations' => $validated['destinations'] ?? [],
      ],
    ]);
  }

  /** @param array<string, mixed> $validated */
  private function syncSourcesAndDestinations(BackupProfile $profile, array $validated): void
  {
    $profile->sources()->delete();
    foreach ($validated['sources'] as $sourceData) {
      $source = BackupSource::create([
        'backup_profile_id' => $profile->id,
        'name' => trim((string) $sourceData['name']),
        'path' => trim((string) $sourceData['path']),
        'is_active' => true,
      ]);

      foreach ($sourceData['exclusions'] ?? [] as $pattern) {
        $trimmed = trim((string) $pattern);
        if ($trimmed === '') {
          continue;
        }
        BackupExclusion::create([
          'backup_source_id' => $source->id,
          'pattern' => $trimmed,
          'pattern_type' => 'glob',
        ]);
      }
    }

    $profile->destinations()->delete();
    foreach ($validated['destinations'] ?? [] as $destData) {
      $account = StorageAccount::where('uid', $destData['storage_account_uid'])->first();
      if ($account === null) {
        continue;
      }

      BackupProfileDestination::create([
        'backup_profile_id' => $profile->id,
        'storage_account_id' => $account->id,
        'remote_folder_id' => trim((string) $destData['remote_folder_id']),
        'is_required' => (bool) ($destData['is_required'] ?? true),
      ]);
    }
  }

  /** @param array<string, mixed> $validated */
  private function syncSchedule(BackupProfile $profile, array $validated): void
  {
    $enabled = (bool) ($validated['schedule_enabled'] ?? false);
    $cronExpr = trim((string) ($validated['cron_expression'] ?? '0 2 * * *'));
    $timezone = trim((string) ($validated['timezone'] ?? 'Asia/Jakarta'));

    $nextRunAt = null;
    if ($enabled && $cronExpr !== '') {
      try {
        $cron = new CronExpression($cronExpr);
        $nextRunAt = $cron->getNextRunDate('now', 0, false, $timezone ?: 'UTC');
      } catch (Throwable) {
        $nextRunAt = now()->addDay();
      }
    }

    BackupSchedule::updateOrCreate(
      ['backup_profile_id' => $profile->id],
      [
        'schedule_type' => (string) ($validated['schedule_type'] ?? 'daily'),
        'cron_expression' => $cronExpr !== '' ? $cronExpr : '0 2 * * *',
        'timezone' => $timezone !== '' ? $timezone : 'Asia/Jakarta',
        'next_run_at' => $nextRunAt,
        'is_enabled' => $enabled,
      ],
    );
  }
}
