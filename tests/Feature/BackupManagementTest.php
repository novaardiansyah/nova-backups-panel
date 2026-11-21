<?php

namespace Tests\Feature;

use App\Enums\BackupStatus;
use App\Enums\BackupType;
use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\BackupStorage;
use App\Models\BackupStorageProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_backup_providers_storages_schedules_and_backups(): void
    {
        $user = User::create([
            'name' => 'nova',
            'email' => 'nova@example.com',
            'password' => null,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('client.backup-storage-providers.store'), [
                'name' => 'Cloudflare R2',
                'slug' => 'cloudflare-r2',
                'keys' => ['endpoint' => 'https://example.r2.cloudflarestorage.com'],
            ])
            ->assertRedirect();

        $provider = BackupStorageProvider::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('cloudflare-r2', $provider->slug);

        $this->actingAs($user)
            ->post(route('client.backup-storages.store'), [
                'name' => 'Primary Bucket',
                'slug' => 'primary-bucket',
                'provider_id' => $provider->id,
                'active' => true,
                'keys' => ['bucket' => 'nova-backups'],
            ])
            ->assertRedirect();

        $storage = BackupStorage::where('user_id', $user->id)->firstOrFail();
        $this->assertSame($provider->id, $storage->provider_id);

        $this->actingAs($user)
            ->post(route('client.backup-schedules.store'), [
                'name' => 'Daily Web Files',
                'type' => BackupType::Files->value,
                'filename_pattern' => 'backup-{Ymd-His}',
                'source_path' => '/www/wwwroot',
                'include' => 'app, config',
                'exclude' => 'vendor, node_modules',
                'local_destination_path' => '/www/backup/sysadmin/others',
                'is_sync_cloud' => true,
                'storage_id' => $storage->id,
                'interval_value' => 1,
                'interval_unit' => 'days',
                'max_count_backup' => 5,
                'is_enabled' => true,
            ])
            ->assertRedirect();

        $schedule = BackupSchedule::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(['app', 'config'], $schedule->include);
        $this->assertSame(['vendor', 'node_modules'], $schedule->exclude);

        $backup = Backup::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'storage_id' => $storage->id,
            'file_name' => 'backup-20261121.zip',
            'file_size' => 1024,
            'type' => BackupType::Files->value,
            'status' => BackupStatus::Success->value,
        ]);

        $this->actingAs($user)->get(route('client.area'))->assertOk();

        $otherUser = User::create([
            'name' => 'other',
            'email' => 'other@example.com',
            'password' => null,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($otherUser)
            ->delete(route('client.backups.destroy', $backup))
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('client.backups.destroy', $backup))
            ->assertRedirect();

        $this->assertSoftDeleted('backups', ['id' => $backup->id]);
    }
}
