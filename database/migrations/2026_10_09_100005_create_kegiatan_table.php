<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('lokasi')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->enum('tipe', ['internal', 'kolaborasi'])->default('internal');
            $table->uuid('token')->nullable()->unique();
            $table->boolean('sertifikat_aktif')->default(false);
            $table->text('sertifikat_url')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
