<?php

namespace App\Http\Controllers;

use App\Enums\BackupScheduleIntervalUnit;
use App\Enums\BackupType;
use App\Models\BackupSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BackupScheduleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSchedule($request);
        $userId = $request->user()->id;

        BackupSchedule::create([
            ...$validated,
            'user_id' => $userId,
        ]);

        return back()->with('status', 'Backup schedule created.');
    }

    public function update(Request $request, BackupSchedule $schedule): RedirectResponse
    {
        abort_unless((int) $schedule->user_id === (int) $request->user()->id, 403);

        $validated = $this->validateSchedule($request);
        $schedule->update($validated);

        return back()->with('status', 'Backup schedule updated.');
    }

    public function destroy(Request $request, BackupSchedule $schedule): RedirectResponse
    {
        abort_unless((int) $schedule->user_id === (int) $request->user()->id, 403);

        $schedule->delete();

        return back()->with('status', 'Backup schedule deleted.');
    }

    private function validateSchedule(Request $request): array
    {
        $userId = $request->user()->id;
        $isDatabase = $request->input('type') === BackupType::Database->value;
        $isSyncCloud = $request->boolean('is_sync_cloud');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in([BackupType::Files->value, BackupType::Database->value])],
            'filename_pattern' => ['required', 'string', 'max:255'],
            'source_path' => [$isDatabase ? 'nullable' : 'required', 'string', 'max:1000'],
            'drivers' => [$isDatabase ? 'required' : 'nullable', 'string', 'max:50'],
            'database_name' => [$isDatabase ? 'required' : 'nullable', 'string', 'max:255'],
            'include' => ['nullable', 'string', 'max:2000'],
            'exclude' => ['nullable', 'string', 'max:2000'],
            'local_destination_path' => ['nullable', 'string', 'max:1000'],
            'r2_destination_path' => ['nullable', 'string', 'max:1000'],
            'keep_local_backup' => ['boolean'],
            'is_sync_cloud' => ['boolean'],
            'server_id' => [
                'nullable',
                'integer',
                Rule::exists('backup_storages', 'id')->where('user_id', $userId),
            ],
            'storage_id' => [
                $isSyncCloud ? 'required' : 'nullable',
                'integer',
                Rule::exists('backup_storages', 'id')->where('user_id', $userId),
            ],
            'interval_value' => ['required', 'integer', 'min:1'],
            'interval_unit' => ['required', Rule::in(array_column(BackupScheduleIntervalUnit::cases(), 'value'))],
            'max_count_backup' => ['required', 'integer', 'min:1'],
            'is_enabled' => ['boolean'],
        ]);

        return [
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'filename_pattern' => trim($validated['filename_pattern']),
            'source_path' => $isDatabase ? null : ($validated['source_path'] ?? null),
            'drivers' => $isDatabase ? ($validated['drivers'] ?? null) : null,
            'database_name' => $isDatabase ? ($validated['database_name'] ?? null) : null,
            'include' => $this->splitCsv($validated['include'] ?? null),
            'exclude' => $this->splitCsv($validated['exclude'] ?? null),
            'local_destination_path' => $validated['local_destination_path'] ?: '/www/backup/sysadmin/others',
            'r2_destination_path' => $isSyncCloud ? ($validated['r2_destination_path'] ?? null) : null,
            'keep_local_backup' => (bool) ($validated['keep_local_backup'] ?? false),
            'is_sync_cloud' => $isSyncCloud,
            'server_id' => $validated['server_id'] ?? null,
            'storage_id' => $isSyncCloud ? ($validated['storage_id'] ?? null) : null,
            'interval_value' => (int) $validated['interval_value'],
            'interval_unit' => $validated['interval_unit'],
            'max_count_backup' => (int) $validated['max_count_backup'],
            'is_enabled' => (bool) ($validated['is_enabled'] ?? true),
        ];
    }

    private function splitCsv(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $items = array_values(array_filter(array_map('trim', explode(',', $value)), fn (string $item) => $item !== ''));

        return $items === [] ? null : $items;
    }
}
