<?php

namespace App\Http\Controllers;

use App\Models\BackupStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BackupStorageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $userId = (int) $request->user()->id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('backup_storages', 'slug')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at'),
            ],
            'provider_id' => [
                'nullable',
                'integer',
                Rule::exists('backup_storage_providers', 'id')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at'),
            ],
            'keys' => ['nullable', 'array'],
            'active' => ['boolean'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        Validator::make(
            ['slug' => $slug],
            [
                'slug' => [
                    Rule::unique('backup_storages', 'slug')
                        ->where('user_id', $userId)
                        ->whereNull('deleted_at'),
                ],
            ],
        )->validate();

        BackupStorage::create([
            'user_id' => $userId,
            'provider_id' => $validated['provider_id'] ?? null,
            'name' => trim($validated['name']),
            'slug' => $slug,
            'keys' => $this->normalizeKeys($validated['keys'] ?? []),
            'active' => (bool) ($validated['active'] ?? true),
        ]);

        return back()->with('status', 'Backup storage created.');
    }

    public function update(Request $request, BackupStorage $storage): RedirectResponse
    {
        $userId = (int) $request->user()->id;
        abort_unless((int) $storage->user_id === $userId, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('backup_storages', 'slug')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at')
                    ->ignore($storage->id),
            ],
            'provider_id' => [
                'nullable',
                'integer',
                Rule::exists('backup_storage_providers', 'id')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at'),
            ],
            'keys' => ['nullable', 'array'],
            'active' => ['boolean'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        $storage->update([
            'provider_id' => $validated['provider_id'] ?? null,
            'name' => trim($validated['name']),
            'slug' => $slug,
            'keys' => $this->normalizeKeys($validated['keys'] ?? []),
            'active' => (bool) ($validated['active'] ?? true),
        ]);

        return back()->with('status', 'Backup storage updated.');
    }

    public function destroy(Request $request, BackupStorage $storage): RedirectResponse
    {
        abort_unless((int) $storage->user_id === (int) $request->user()->id, 403);

        $storage->update([
            'slug' => $storage->slug.'-deleted-'.$storage->id,
        ]);

        $storage->delete();

        return back()->with('status', 'Backup storage deleted.');
    }

    private function normalizeKeys(array $keys): array
    {
        $normalized = [];
        foreach ($keys as $key => $value) {
            $cleanKey = trim((string) $key);
            if ($cleanKey !== '') {
                $normalized[$cleanKey] = $value;
            }
        }

        return $normalized;
    }
}
