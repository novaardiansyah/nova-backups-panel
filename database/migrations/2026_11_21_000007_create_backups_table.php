<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('storage_id')->nullable()->constrained('backup_storages')->noActionOnDelete();
            $table->foreignId('server_id')->nullable()->constrained('backup_storages')->noActionOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('backup_schedules')->noActionOnDelete();
            $table->string('uid')->unique();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('cloud_file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->string('type')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration')->default(0);
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
