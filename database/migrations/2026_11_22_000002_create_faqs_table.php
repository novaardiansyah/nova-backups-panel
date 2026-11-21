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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('question_en');
            $table->string('question_id');
            $table->text('answer_en');
            $table->text('answer_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $now = now();
        $defaults = [
            [
                'uid' => (string) Str::uuid7(),
                'question_en' => 'How does Nova Backups Panel work with the Backup Agent?',
                'question_id' => 'Bagaimana cara kerja Nova Backups Panel dengan Backup Agent?',
                'answer_en' => 'Nova Backups Panel serves as the centralized web management UI where you configure storage providers, destinations, and backup schedules. Your external Backup Agent reads these schedules, executes the file or database backups, and reports execution records back.',
                'answer_id' => 'Nova Backups Panel berfungsi sebagai UI manajemen terpusat untuk mengatur storage provider, destinasi, dan jadwal backup. Backup Agent eksternal membaca jadwal tersebut, menjalankan backup file atau database, lalu mengirimkan catatan eksekusi kembali.',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uid' => (string) Str::uuid7(),
                'question_en' => 'Which cloud storage destinations are supported?',
                'question_id' => 'Destinasi cloud storage apa saja yang didukung?',
                'answer_en' => 'You can define provider templates and storage destinations for cloud targets such as Amazon S3 (and S3-compatible object storage) or Google Drive, as well as local retention paths.',
                'answer_id' => 'Anda dapat membuat template provider dan destinasi penyimpanan untuk target cloud seperti Amazon S3 (dan object storage kompatibel S3) atau Google Drive, serta path retensi lokal.',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uid' => (string) Str::uuid7(),
                'question_en' => 'What types of backups can I schedule?',
                'question_id' => 'Jenis backup apa saja yang dapat dijadwalkan?',
                'answer_en' => 'Schedules support both File backups (with source path, include, and exclude patterns) and Database backups (such as MySQL), each with customizable filename patterns, intervals, and retention counts.',
                'answer_id' => 'Jadwal mendukung backup File (dengan source path, pola include, dan exclude) serta backup Database (seperti MySQL), masing-masing dengan pola nama file, interval, dan batas retensi yang dapat disesuaikan.',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uid' => (string) Str::uuid7(),
                'question_en' => 'Are active tabs and open dialogs preserved on refresh?',
                'question_id' => 'Apakah tab aktif dan dialog yang terbuka tetap dipertahankan saat refresh?',
                'answer_en' => 'Yes. The active menu tab and open create, detail, edit, or delete dialogs are persisted in URL query parameters using each record\'s UUID v7 uid.',
                'answer_id' => 'Ya. Tab menu aktif dan dialog tambah, detail, edit, atau hapus yang sedang dibuka disimpan pada parameter query URL menggunakan UUID v7 uid masing-masing data.',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('faqs')->insert($defaults);
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
