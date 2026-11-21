<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('theme', 20)->default('system');
            $table->string('locale', 10)->default('en');
            $table->timestamps();
        });

        if (Schema::hasColumns('users', ['theme', 'locale'])) {
            $users = DB::table('users')->select('id', 'theme', 'locale')->get();
            foreach ($users as $user) {
                DB::table('user_preferences')->insert([
                    'user_id' => $user->id,
                    'theme' => $user->theme ?? 'system',
                    'locale' => $user->locale ?? 'en',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['theme', 'locale']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumns('users', ['theme', 'locale'])) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('theme', 20)->default('system')->after('password');
                $table->string('locale', 10)->default('en')->after('theme');
            });
        }

        Schema::dropIfExists('user_preferences');
    }
};
