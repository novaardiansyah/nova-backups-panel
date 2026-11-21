<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('storage_id')->nullable()->constrained('backup_storages')->noActionOnDelete();
            $table->foreignId('server_id')->nullable()->constrained('backup_storages')->noActionOnDelete();
            $table->string('uid')->unique();
            $table->string('name');
            $table->string('type')->default('files');
            $table->string('drivers')->nullable();
            $table->string('database_name')->nullable();
            $table->text('source_path')->nullable();
            $table->json('include')->nullable();
            $table->json('exclude')->nullable();
            $table->text('local_destination_path')->nullable();
            $table->text('r2_destination_path')->nullable();
            $table->boolean('keep_local_backup')->default(false);
            $table->boolean('is_sync_cloud')->default(false);
            $table->string('filename_pattern')->default('backup-{Ymd-His}');
            $table->unsignedInteger('count_backup')->default(0);
            $table->unsignedInteger('max_count_backup')->default(3);
            $table->unsignedBigInteger('sum_file_size')->default(0);
            $table->unsignedInteger('interval_value')->default(3);
            $table->string('interval_unit')->default('days');
            $table->timestamp('next_backup_at')->nullable();
            $table->timestamp('last_backup_at')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_schedules');
    }
};
