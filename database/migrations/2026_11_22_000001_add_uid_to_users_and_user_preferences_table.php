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
                    $table->uuid('uid')->nullable()->after('id');
                });
            } elseif (Schema::hasIndex($tableName, "{$tableName}_uid_unique")) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropUnique("{$tableName}_uid_unique");
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
        foreach (['user_preferences', 'users'] as $tableName) {
            if (Schema::hasColumn($tableName, 'uid')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasIndex($tableName, "{$tableName}_uid_unique")) {
                        $table->dropUnique("{$tableName}_uid_unique");
                    }
                    $table->dropColumn('uid');
                });
            }
        }
    }
};
