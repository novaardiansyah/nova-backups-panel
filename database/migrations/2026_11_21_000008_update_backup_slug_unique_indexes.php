<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_storage_providers', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'slug']);
            $table->index(['user_id', 'slug']);
        });

        Schema::table('backup_storages', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'slug']);
            $table->index(['user_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('backup_storage_providers', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'slug']);
            $table->unique(['user_id', 'slug']);
        });

        Schema::table('backup_storages', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'slug']);
            $table->unique(['user_id', 'slug']);
        });
    }
};
