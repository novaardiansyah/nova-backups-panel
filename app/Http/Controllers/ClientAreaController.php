<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\BackupProfile;
use App\Models\BackupRun;
use App\Models\StorageAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ClientAreaController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('preference');

        return Inertia::render('client/ClientArea', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'emailVerifiedAt' => $user->email_verified_at?->toIso8601String(),
                'createdAt' => $user->created_at?->toIso8601String(),
                'hasPassword' => $user->hasPassword(),
                'theme' => $user->preferredTheme(),
                'locale' => $user->preferredLocale(),
            ],
            'showOnboarding' => (bool) $request->session()->get('show_onboarding', false),
            'onboardingCompleted' => (bool) $request->session()->get('onboarding_completed', false),
            'status' => session('status'),
            'agentToken' => session('agent_token'),
            'agents' => Agent::query()
                ->withCount(['backupProfiles', 'backupRuns'])
                ->latest('id')
                ->get()
                ->map(fn (Agent $agent) => [
                    'uid' => $agent->uid,
                    'name' => $agent->name,
                    'hostname' => $agent->hostname,
                    'status' => $agent->status,
                    'agentVersion' => $agent->agent_version,
                    'lastSeenAt' => $agent->last_seen_at?->toIso8601String(),
                    'createdAt' => $agent->created_at?->toIso8601String(),
                    'profilesCount' => $agent->backup_profiles_count,
                    'runsCount' => $agent->backup_runs_count,
                ]),
            'storageAccounts' => StorageAccount::query()
                ->latest('id')
                ->get()
                ->map(fn (StorageAccount $account) => [
                    'uid' => $account->uid,
                    'name' => $account->name,
                    'provider' => $account->provider,
                    'accountEmail' => $account->account_email,
                    'status' => $account->status,
                    'createdAt' => $account->created_at?->toIso8601String(),
                ]),
            'backupProfiles' => BackupProfile::query()
                ->with(['agent:id,uid,name', 'versions', 'sources.exclusions', 'destinations.storageAccount:id,uid,name,account_email', 'schedules'])
                ->latest('id')
                ->get()
                ->map(function (BackupProfile $profile) {
                    $latestVersion = $profile->versions->sortByDesc('version')->first();
                    $schedule = $profile->schedules->first();

                    return [
                        'uid' => $profile->uid,
                        'name' => $profile->name,
                        'description' => $profile->description,
                        'isActive' => $profile->is_active,
                        'agentUid' => $profile->agent?->uid,
                        'agentName' => $profile->agent?->name,
                        'latestVersion' => $latestVersion?->version ?? 1,
                        'encryptArchive' => $latestVersion?->encrypt_archive ?? true,
                        'encryptFileNames' => $latestVersion?->encrypt_file_names ?? true,
                        'compressionLevel' => $latestVersion?->compression_level ?? 5,
                        'keepLocalDefault' => $latestVersion?->keep_local_default ?? false,
                        'sources' => $profile->sources->map(fn ($source) => [
                            'uid' => $source->uid,
                            'name' => $source->name,
                            'path' => $source->path,
                            'exclusions' => $source->exclusions->pluck('pattern')->values()->all(),
                        ])->values()->all(),
                        'destinations' => $profile->destinations->map(fn ($dest) => [
                            'uid' => $dest->uid,
                            'storageAccountUid' => $dest->storageAccount?->uid,
                            'storageAccountName' => $dest->storageAccount?->name,
                            'storageAccountEmail' => $dest->storageAccount?->account_email,
                            'remoteFolderId' => $dest->remote_folder_id,
                            'isRequired' => $dest->is_required,
                        ])->values()->all(),
                        'schedule' => $schedule !== null ? [
                            'isEnabled' => $schedule->is_enabled,
                            'scheduleType' => $schedule->schedule_type,
                            'cronExpression' => $schedule->cron_expression,
                            'timezone' => $schedule->timezone,
                            'nextRunAt' => $schedule->next_run_at?->toIso8601String(),
                        ] : null,
                    ];
                }),
            'backupRuns' => BackupRun::query()
                ->with(['backupProfile:id,uid,name', 'agent:id,uid,name', 'artifacts.uploads.storageAccount:id,uid,name,account_email', 'tasks.jobEvents'])
                ->latest('id')
                ->limit(30)
                ->get()
                ->map(fn (BackupRun $run) => [
                    'uid' => $run->uid,
                    'profileName' => $run->backupProfile?->name,
                    'agentName' => $run->agent?->name,
                    'triggerType' => $run->trigger_type,
                    'status' => $run->status,
                    'startedAt' => $run->started_at?->toIso8601String(),
                    'completedAt' => $run->completed_at?->toIso8601String(),
                    'errorMessage' => $run->error_message,
                    'artifacts' => $run->artifacts->map(fn ($artifact) => [
                        'uid' => $artifact->uid,
                        'filename' => $artifact->filename,
                        'fileSize' => $artifact->file_size,
                        'checksumSha256' => $artifact->checksum_sha256,
                        'localStatus' => $artifact->local_status,
                        'uploads' => $artifact->uploads->map(fn ($upload) => [
                            'uid' => $upload->uid,
                            'status' => $upload->status,
                            'remoteFileId' => $upload->remote_file_id,
                            'storageAccountName' => $upload->storageAccount?->name,
                            'uploadedAt' => $upload->uploaded_at?->toIso8601String(),
                            'errorMessage' => $upload->error_message,
                        ])->values()->all(),
                    ])->values()->all(),
                    'events' => $run->tasks->flatMap(fn ($task) => $task->jobEvents)->sortByDesc('id')->take(10)->map(fn ($ev) => [
                        'uid' => $ev->uid,
                        'eventType' => $ev->event_type,
                        'message' => $ev->message,
                        'createdAt' => $ev->created_at?->toIso8601String(),
                    ])->values()->all(),
                ]),
            'auditLogs' => AuditLog::query()
                ->with('user:id,name,email')
                ->latest('id')
                ->limit(30)
                ->get()
                ->map(fn (AuditLog $log) => [
                    'uid' => $log->uid,
                    'action' => $log->action,
                    'subjectType' => $log->subject_type,
                    'userName' => $log->user?->name ?? 'System',
                    'ipAddress' => $log->ip_address,
                    'createdAt' => $log->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        $wantsPasswordUpdate = $request->filled('password');
        if ($wantsPasswordUpdate) {
            if ($user->hasPassword()) {
                $rules['current_password'] = ['required', 'string'];
            }
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        if ($wantsPasswordUpdate && $user->hasPassword()) {
            if (! Hash::check((string) $validated['current_password'], (string) $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'The current password is incorrect.',
                ]);
            }
        }

        $attributes = [
            'name' => trim($validated['name']),
        ];

        if ($wantsPasswordUpdate) {
            $attributes['password'] = Hash::make($validated['password']);
        }

        $user->forceFill($attributes)->save();

        return back()->with('status', 'Profile updated successfully.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
            'locale' => ['required', 'string', 'in:en,id'],
        ]);

        $user->preference()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'theme' => $validated['theme'],
                'locale' => $validated['locale'],
            ],
        );

        app()->setLocale($validated['locale']);

        return back()->with(
            'status',
            $validated['locale'] === 'id'
                ? 'Preferensi berhasil disimpan.'
                : 'Preferences saved successfully.',
        );
    }

    public function completeOnboarding(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        $wantsPassword = $request->filled('password');
        if ($wantsPassword) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        $attributes = [
            'name' => trim($validated['name']),
        ];

        if ($wantsPassword) {
            $attributes['password'] = Hash::make($validated['password']);
        }

        $user->forceFill($attributes)->save();

        $request->session()->forget('show_onboarding');
        $request->session()->flash('onboarding_completed', true);

        return redirect()->route('client.area');
    }

    public function skipOnboarding(Request $request): RedirectResponse
    {
        $request->session()->forget('show_onboarding');

        return redirect()->route('client.area');
    }
}
