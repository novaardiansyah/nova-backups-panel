<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'user_preferences'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'uid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->uuid('uid')->nullable()->unique()->after('id');
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
        }
    }

    public function down(): void
    {
        foreach (['user_preferences', 'users'] as $tableName) {
            if (Schema::hasColumn($tableName, 'uid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropUnique(['uid']);
                    $table->dropColumn('uid');
                });
            }
        }
    }
};
