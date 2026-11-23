<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('agents', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->unique();
      $table->string('name', 150);
      $table->string('hostname', 255);
      $table->string('status', 20)->default('offline');
      $table->string('agent_version', 30)->nullable();
      $table->dateTime('last_seen_at')->nullable();
      $table->dateTime('created_at')->nullable();
    });

    Schema::create('agent_credentials', function (Blueprint $table) {
      $table->id();
      $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
      $table->string('token_hash', 64)->unique();
      $table->dateTime('expires_at')->nullable();
      $table->dateTime('revoked_at')->nullable();
    });

    Schema::create('backup_profiles', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->unique();
      $table->string('name', 150);
      $table->text('description')->nullable();
      $table->foreignId('agent_id')->constrained('agents')->noActionOnDelete();
      $table->boolean('is_active')->default(true);
      $table->dateTime('created_at')->nullable();
    });

    Schema::create('backup_profile_versions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('backup_profile_id')->constrained('backup_profiles')->cascadeOnDelete();
      $table->integer('version');
      $table->string('archive_format', 10)->default('rar');
      $table->boolean('encrypt_archive')->default(true);
      $table->boolean('encrypt_file_names')->default(true);
      $table->text('password_ciphertext')->nullable();
      $table->unsignedTinyInteger('compression_level')->default(5);
      $table->boolean('keep_local_default')->default(false);
      $table->text('configuration')->nullable();
      $table->dateTime('created_at')->nullable();

      $table->unique(['backup_profile_id', 'version']);
    });

    Schema::create('backup_sources', function (Blueprint $table) {
      $table->id();
      $table->foreignId('backup_profile_id')->constrained('backup_profiles')->cascadeOnDelete();
      $table->string('name', 150);
      $table->string('path', 1000);
      $table->boolean('is_active')->default(true);
    });

    Schema::create('backup_exclusions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('backup_source_id')->constrained('backup_sources')->cascadeOnDelete();
      $table->string('pattern', 1000);
      $table->string('pattern_type', 20)->default('glob');
    });

    Schema::create('storage_accounts', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->unique();
      $table->string('name', 150);
      $table->string('provider', 30)->default('google_drive');
      $table->string('account_email', 255);
      $table->string('status', 20)->default('connected');
      $table->dateTime('created_at')->nullable();
    });

    Schema::create('storage_credentials', function (Blueprint $table) {
      $table->id();
      $table->foreignId('storage_account_id')->constrained('storage_accounts')->cascadeOnDelete();
      $table->text('access_token');
      $table->text('refresh_token')->nullable();
      $table->dateTime('token_expires_at')->nullable();
      $table->dateTime('updated_at')->nullable();
    });

    Schema::create('backup_profile_destinations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('backup_profile_id')->constrained('backup_profiles')->cascadeOnDelete();
      $table->foreignId('storage_account_id')->constrained('storage_accounts')->noActionOnDelete();
      $table->string('remote_folder_id', 255);
      $table->boolean('is_required')->default(true);
    });

    Schema::create('backup_schedules', function (Blueprint $table) {
      $table->id();
      $table->foreignId('backup_profile_id')->constrained('backup_profiles')->cascadeOnDelete();
      $table->string('schedule_type', 20)->default('daily');
      $table->string('cron_expression', 100);
      $table->string('timezone', 100)->default('Asia/Jakarta');
      $table->dateTime('next_run_at')->nullable();
      $table->boolean('is_enabled')->default(true);
    });

    Schema::create('backup_runs', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->unique();
      $table->foreignId('backup_profile_id')->constrained('backup_profiles')->noActionOnDelete();
      $table->foreignId('profile_version_id')->constrained('backup_profile_versions')->noActionOnDelete();
      $table->foreignId('agent_id')->constrained('agents')->noActionOnDelete();
      $table->string('trigger_type', 20)->default('scheduled');
      $table->string('status', 20)->default('pending');
      $table->dateTime('started_at')->nullable();
      $table->dateTime('completed_at')->nullable();
      $table->text('error_message')->nullable();
    });

    Schema::create('backup_run_destinations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('backup_run_id')->constrained('backup_runs')->cascadeOnDelete();
      $table->foreignId('storage_account_id')->constrained('storage_accounts')->noActionOnDelete();
      $table->string('remote_folder_id', 255);
      $table->boolean('is_required')->default(true);
    });

    Schema::create('backup_run_attempts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('backup_run_id')->constrained('backup_runs')->cascadeOnDelete();
      $table->integer('attempt_number');
      $table->string('status', 20)->default('running');
      $table->dateTime('started_at')->nullable();
      $table->dateTime('completed_at')->nullable();
      $table->text('error_message')->nullable();
    });

    Schema::create('backup_artifacts', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->unique();
      $table->foreignId('backup_run_id')->constrained('backup_runs')->cascadeOnDelete();
      $table->string('filename', 255);
      $table->string('local_path', 1000);
      $table->unsignedBigInteger('file_size')->default(0);
      $table->string('checksum_sha256', 64)->nullable();
      $table->boolean('keep_local')->default(false);
      $table->string('local_status', 20)->default('available');
      $table->dateTime('created_at')->nullable();
    });

    Schema::create('backup_uploads', function (Blueprint $table) {
      $table->id();
      $table->foreignId('backup_artifact_id')->constrained('backup_artifacts')->cascadeOnDelete();
      $table->foreignId('storage_account_id')->constrained('storage_accounts')->noActionOnDelete();
      $table->string('status', 20)->default('pending');
      $table->string('remote_file_id', 255)->nullable();
      $table->string('remote_filename', 255)->nullable();
      $table->unsignedBigInteger('uploaded_size')->default(0);
      $table->dateTime('uploaded_at')->nullable();
      $table->text('error_message')->nullable();
    });

    Schema::create('agent_tasks', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->unique();
      $table->foreignId('agent_id')->constrained('agents')->noActionOnDelete();
      $table->foreignId('backup_run_id')->constrained('backup_runs')->cascadeOnDelete();
      $table->string('task_type', 30)->default('create_archive');
      $table->string('status', 20)->default('pending');
      $table->smallInteger('priority')->default(5);
      $table->integer('attempts')->default(0);
      $table->integer('max_attempts')->default(3);
      $table->uuid('lease_token')->nullable();
      $table->dateTime('lease_expires_at')->nullable();
      $table->dateTime('started_at')->nullable();
      $table->dateTime('completed_at')->nullable();
    });

    Schema::create('backup_job_events', function (Blueprint $table) {
      $table->id();
      $table->foreignId('agent_task_id')->constrained('agent_tasks')->cascadeOnDelete();
      $table->string('event_type', 50);
      $table->text('message')->nullable();
      $table->text('metadata')->nullable();
      $table->dateTime('created_at')->nullable();
    });

    Schema::create('agent_events', function (Blueprint $table) {
      $table->id();
      $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
      $table->string('event_type', 50);
      $table->text('message')->nullable();
      $table->text('metadata')->nullable();
      $table->dateTime('created_at')->nullable();
    });

    Schema::create('audit_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->string('action', 100);
      $table->string('subject_type', 100);
      $table->unsignedBigInteger('subject_id')->nullable();
      $table->string('ip_address', 45)->nullable();
      $table->text('metadata')->nullable();
      $table->dateTime('created_at')->nullable();

      $table->index(['subject_type', 'subject_id']);
    });

    Schema::create('outbox_messages', function (Blueprint $table) {
      $table->id();
      $table->string('event_type', 100);
      $table->string('aggregate_type', 100);
      $table->unsignedBigInteger('aggregate_id');
      $table->text('payload');
      $table->string('status', 20)->default('pending');
      $table->dateTime('published_at')->nullable();
      $table->dateTime('created_at')->nullable();

      $table->index(['status', 'created_at']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('outbox_messages');
    Schema::dropIfExists('audit_logs');
    Schema::dropIfExists('agent_events');
    Schema::dropIfExists('backup_job_events');
    Schema::dropIfExists('agent_tasks');
    Schema::dropIfExists('backup_uploads');
    Schema::dropIfExists('backup_artifacts');
    Schema::dropIfExists('backup_run_attempts');
    Schema::dropIfExists('backup_run_destinations');
    Schema::dropIfExists('backup_runs');
    Schema::dropIfExists('backup_schedules');
    Schema::dropIfExists('backup_profile_destinations');
    Schema::dropIfExists('storage_credentials');
    Schema::dropIfExists('storage_accounts');
    Schema::dropIfExists('backup_exclusions');
    Schema::dropIfExists('backup_sources');
    Schema::dropIfExists('backup_profile_versions');
    Schema::dropIfExists('backup_profiles');
    Schema::dropIfExists('agent_credentials');
    Schema::dropIfExists('agents');
  }
};
