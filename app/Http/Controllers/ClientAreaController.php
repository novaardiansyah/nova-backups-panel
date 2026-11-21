<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\BackupStorage;
use App\Models\BackupStorageProvider;
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
            'backups' => Backup::query()
                ->where('user_id', $user->id)
                ->with(['schedule:id,name', 'storage:id,name', 'server:id,name'])
                ->latest('id')
                ->get()
                ->map(fn (Backup $backup) => [
                    'id' => $backup->id,
                    'uid' => $backup->uid,
                    'fileName' => $backup->file_name,
                    'fileSize' => $backup->file_size,
                    'type' => $backup->type?->value,
                    'status' => $backup->status?->value ?? 'pending',
                    'duration' => $backup->duration,
                    'message' => $backup->message,
                    'scheduleName' => $backup->schedule?->name,
                    'storageName' => $backup->storage?->name,
                    'serverName' => $backup->server?->name,
                    'startedAt' => $backup->started_at?->toIso8601String(),
                    'completedAt' => $backup->completed_at?->toIso8601String(),
                ]),
            'backupSchedules' => BackupSchedule::query()
                ->where('user_id', $user->id)
                ->with(['storage:id,name', 'server:id,name'])
                ->latest('id')
                ->get()
                ->map(fn (BackupSchedule $schedule) => [
                    'id' => $schedule->id,
                    'uid' => $schedule->uid,
                    'name' => $schedule->name,
                    'type' => $schedule->type?->value ?? 'files',
                    'drivers' => $schedule->drivers,
                    'databaseName' => $schedule->database_name,
                    'sourcePath' => $schedule->source_path,
                    'include' => $schedule->include ?? [],
                    'exclude' => $schedule->exclude ?? [],
                    'localDestinationPath' => $schedule->local_destination_path,
                    'r2DestinationPath' => $schedule->r2_destination_path,
                    'keepLocalBackup' => $schedule->keep_local_backup,
                    'isSyncCloud' => $schedule->is_sync_cloud,
                    'filenamePattern' => $schedule->filename_pattern,
                    'countBackup' => $schedule->count_backup,
                    'maxCountBackup' => $schedule->max_count_backup,
                    'sumFileSize' => $schedule->sum_file_size,
                    'intervalValue' => $schedule->interval_value,
                    'intervalUnit' => $schedule->interval_unit?->value ?? 'days',
                    'nextBackupAt' => $schedule->next_backup_at?->toIso8601String(),
                    'lastBackupAt' => $schedule->last_backup_at?->toIso8601String(),
                    'isEnabled' => $schedule->is_enabled,
                    'storageId' => $schedule->storage_id,
                    'serverId' => $schedule->server_id,
                    'storageName' => $schedule->storage?->name,
                    'serverName' => $schedule->server?->name,
                ]),
            'backupStorages' => BackupStorage::query()
                ->where('user_id', $user->id)
                ->with('provider:id,name')
                ->latest('id')
                ->get()
                ->map(fn (BackupStorage $storage) => [
                    'id' => $storage->id,
                    'uid' => $storage->uid,
                    'name' => $storage->name,
                    'slug' => $storage->slug,
                    'providerId' => $storage->provider_id,
                    'providerName' => $storage->provider?->name,
                    'keys' => $storage->keys ?? [],
                    'active' => $storage->active,
                ]),
            'backupStorageProviders' => BackupStorageProvider::query()
                ->where('user_id', $user->id)
                ->withCount('storages')
                ->latest('id')
                ->get()
                ->map(fn (BackupStorageProvider $provider) => [
                    'id' => $provider->id,
                    'uid' => $provider->uid,
                    'name' => $provider->name,
                    'slug' => $provider->slug,
                    'keys' => $provider->keys ?? [],
                    'storagesCount' => $provider->storages_count,
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
