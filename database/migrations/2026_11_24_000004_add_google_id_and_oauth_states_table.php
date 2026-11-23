<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->string('google_id')->nullable()->index()->after('email');
    });

    Schema::create('oauth_states', function (Blueprint $table) {
      $table->id();
      $table->uuid('uid')->unique();
      $table->string('state', 64)->unique();
      $table->string('purpose', 30)->default('auth');
      $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->dateTime('expires_at');
      $table->dateTime('created_at')->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('oauth_states');

    Schema::table('users', function (Blueprint $table) {
      $table->dropIndex(['google_id']);
      $table->dropColumn('google_id');
    });
  }
};
