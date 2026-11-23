<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
  /** @var list<string> */
  private array $tablesWithUuid = [
    'agents',
    'backup_profiles',
    'storage_accounts',
    'backup_runs',
    'backup_artifacts',
    'agent_tasks',
  ];

  /** @var list<string> */
  private array $allTables = [
    'agents',
    'agent_credentials',
    'backup_profiles',
    'backup_profile_versions',
    'backup_sources',
    'backup_exclusions',
    'storage_accounts',
    'storage_credentials',
    'backup_profile_destinations',
    'backup_schedules',
    'backup_runs',
    'backup_run_destinations',
    'backup_run_attempts',
    'backup_artifacts',
    'backup_uploads',
    'agent_tasks',
    'backup_job_events',
    'agent_events',
    'audit_logs',
    'outbox_messages',
  ];

  public function up(): void
  {
    foreach ($this->tablesWithUuid as $tableName) {
      if (Schema::hasColumn($tableName, 'uuid')) {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
          if (Schema::hasIndex($tableName, "{$tableName}_uuid_unique")) {
            $table->dropUnique("{$tableName}_uuid_unique");
          }
          $table->dropColumn('uuid');
        });
      }
    }

    foreach ($this->allTables as $tableName) {
      if (! Schema::hasColumn($tableName, 'uid')) {
        Schema::table($tableName, function (Blueprint $table) {
          $table->uuid('uid')->nullable();
        });
      }

      $rows = DB::table($tableName)->whereNull('uid')->select('id')->get();
      foreach ($rows as $row) {
        DB::table($tableName)
          ->where('id', $row->id)
          ->update(['uid' => (string) Str::uuid7()]);
      }

      Schema::table($tableName, function (Blueprint $table) {
        $table->uuid('uid')->nullable(false)->change();
      });

      if (! Schema::hasIndex($tableName, "{$tableName}_uid_unique")) {
        Schema::table($tableName, function (Blueprint $table) {
          $table->unique('uid');
        });
      }
    }
  }

  public function down(): void
  {
    foreach (array_reverse($this->allTables) as $tableName) {
      if (Schema::hasColumn($tableName, 'uid')) {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
          if (Schema::hasIndex($tableName, "{$tableName}_uid_unique")) {
            $table->dropUnique("{$tableName}_uid_unique");
          }
          $table->dropColumn('uid');
        });
      }
    }

    foreach (array_reverse($this->tablesWithUuid) as $tableName) {
      if (! Schema::hasColumn($tableName, 'uuid')) {
        Schema::table($tableName, function (Blueprint $table) {
          $table->uuid('uuid')->nullable();
        });
      }
    }
  }
};
