<?php

namespace App\Http\Controllers;

use App\Models\BackupStorageProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BackupStorageProviderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('backup_storage_providers', 'slug')->where('user_id', $userId),
            ],
            'keys' => ['nullable', 'array'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        $request->validate([
            'slug' => [
                Rule::unique('backup_storage_providers', 'slug')->where('user_id', $userId),
            ],
        ]);

        BackupStorageProvider::create([
            'user_id' => $userId,
            'name' => trim($validated['name']),
            'slug' => $slug,
            'keys' => $this->normalizeKeys($validated['keys'] ?? []),
        ]);

        return back()->with('status', 'Storage provider created.');
    }

    public function update(Request $request, BackupStorageProvider $provider): RedirectResponse
    {
        $userId = (int) $request->user()->id;
        abort_unless((int) $provider->user_id === $userId, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('backup_storage_providers', 'slug')
                    ->where('user_id', $userId)
                    ->ignore($provider->id),
            ],
            'keys' => ['nullable', 'array'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        $provider->update([
            'name' => trim($validated['name']),
            'slug' => $slug,
            'keys' => $this->normalizeKeys($validated['keys'] ?? []),
        ]);

        return back()->with('status', 'Storage provider updated.');
    }

    public function destroy(Request $request, BackupStorageProvider $provider): RedirectResponse
    {
        abort_unless((int) $provider->user_id === (int) $request->user()->id, 403);

        $provider->delete();

        return back()->with('status', 'Storage provider deleted.');
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
