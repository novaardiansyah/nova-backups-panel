<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentCredential;
use App\Models\BackupProfile;
use App\Models\BackupRun;
use App\Models\OAuthState;
use App\Models\StorageAccount;
use App\Models\StorageCredential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class PlanningV2EndToEndTest extends TestCase
{
  use RefreshDatabase;

  public function test_google_auth_redirect_and_callback_logs_in_user(): void
  {
    $response = $this->get('/auth/google/redirect');
    $response->assertRedirect();

    $oauthState = OAuthState::where('purpose', 'auth')->firstOrFail();

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getEmail')->andReturn('google-user@example.com');
    $socialiteUser->shouldReceive('getId')->andReturn('google-123456');
    $socialiteUser->shouldReceive('getName')->andReturn('Google User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('guser');

    $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('redirectUrl')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $callback = $this->get('/google/callback?state='.$oauthState->state.'&code=auth-code');
    $callback->assertRedirect(route('client.area'));

    $this->assertAuthenticated();
    $user = User::where('email', 'google-user@example.com')->firstOrFail();
    $this->assertSame('google-123456', $user->google_id);
    $this->assertNotNull($user->email_verified_at);
  }

  public function test_full_end_to_end_backup_orchestration_and_agent_api(): void
  {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/client/agents', [
      'name' => 'Prod Node 1',
      'hostname' => 'prod-01.local',
    ])->assertRedirect();

    $plainToken = session('agent_token');
    $this->assertIsString($plainToken);
    $this->assertStringStartsWith('nba_', $plainToken);

    $agent = Agent::where('hostname', 'prod-01.local')->firstOrFail();
    $this->assertTrue(
      AgentCredential::where('agent_id', $agent->id)
        ->where('token_hash', hash('sha256', $plainToken))
        ->exists(),
    );

    $storage = StorageAccount::create([
      'name' => 'Google Drive Primary',
      'provider' => 'google_drive',
      'account_email' => 'drive@example.com',
      'status' => 'connected',
    ]);

    StorageCredential::create([
      'storage_account_id' => $storage->id,
      'access_token' => 'ya29.mock-access-token',
      'refresh_token' => '1//mock-refresh-token',
      'token_expires_at' => now()->addHour(),
    ]);

    $this->actingAs($user)->post('/client/backup-profiles', [
      'name' => 'Web App Backup',
      'description' => 'Daily production backup',
      'agent_uid' => $agent->uid,
      'is_active' => true,
      'encrypt_archive' => true,
      'encrypt_file_names' => true,
      'rar_password' => 'super-secret-rar',
      'compression_level' => 5,
      'keep_local_default' => false,
      'sources' => [
        [
          'name' => 'App Root',
          'path' => '/var/www/html',
          'exclusions' => ['node_modules/*', 'storage/logs/*'],
        ],
      ],
      'destinations' => [
        [
          'storage_account_uid' => $storage->uid,
          'remote_folder_id' => 'folder-123',
          'is_required' => true,
        ],
      ],
      'schedule_enabled' => true,
      'schedule_type' => 'daily',
      'cron_expression' => '0 2 * * *',
      'timezone' => 'Asia/Jakarta',
    ])->assertRedirect();

    $profile = BackupProfile::where('name', 'Web App Backup')->firstOrFail();

    $this->actingAs($user)->post("/client/backup-profiles/{$profile->uid}/run")->assertRedirect();

    $headers = ['Authorization' => "Bearer {$plainToken}"];

    $this->postJson('/api/v1/agent/heartbeat', [
      'hostname' => 'prod-01.local',
      'agent_version' => '1.0.0',
      'status' => 'online',
    ], $headers)->assertOk()->assertJson(['ok' => true]);

    $claim = $this->postJson('/api/v1/agent/tasks/claim', [], $headers)
      ->assertOk()
      ->assertJsonPath('task.profile_name', 'Web App Backup')
      ->assertJsonPath('task.profile_version.rar_password', 'super-secret-rar')
      ->assertJsonPath('task.destinations.0.remote_folder_id', 'folder-123');

    $taskUid = $claim->json('task.uid');
    $leaseToken = $claim->json('task.lease_token');

    $this->postJson("/api/v1/agent/tasks/{$taskUid}/events", [
      'lease_token' => $leaseToken,
      'event_type' => 'archive_started',
      'message' => 'Creating RAR archive',
    ], $headers)->assertOk();

    $this->postJson("/api/v1/agent/tasks/{$taskUid}/complete", [
      'lease_token' => $leaseToken,
      'artifact' => [
        'filename' => 'web-app-backup-20261124.rar',
        'local_path' => '/tmp/nova-backups/web-app-backup-20261124.rar',
        'file_size' => 102400,
        'checksum_sha256' => str_repeat('a', 64),
        'keep_local' => false,
        'local_status' => 'deleted',
      ],
      'uploads' => [
        [
          'storage_account_uid' => $storage->uid,
          'status' => 'completed',
          'remote_file_id' => 'gdrive-file-999',
          'remote_filename' => 'web-app-backup-20261124.rar',
          'uploaded_size' => 102400,
        ],
      ],
    ], $headers)->assertOk();

    $run = BackupRun::where('backup_profile_id', $profile->id)->firstOrFail();
    $this->assertSame('completed', $run->status);
    $this->assertDatabaseHas('backup_artifacts', ['backup_run_id' => $run->id, 'local_status' => 'deleted']);
    $this->assertDatabaseHas('backup_uploads', ['remote_file_id' => 'gdrive-file-999', 'status' => 'completed']);
  }
}
