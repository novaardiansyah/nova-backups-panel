<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('backups');
        Schema::dropIfExists('backup_schedules');
        Schema::dropIfExists('backup_storages');
        Schema::dropIfExists('backup_storage_providers');
    }

    public function down(): void {}
};
