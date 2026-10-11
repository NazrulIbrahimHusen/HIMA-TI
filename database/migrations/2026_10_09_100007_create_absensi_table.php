<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_absensi')->onDelete('cascade');
            $table->foreignId('anggota_id')->nullable()->constrained('anggota')->nullOnDelete();
            $table->string('nim', 10);
            $table->string('nama');
            $table->enum('tipe_peserta', ['anggota', 'tamu'])->default('anggota');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa'])->default('alpa');
            $table->enum('metode', ['qr', 'link', 'manual'])->default('manual');
            $table->dateTime('waktu_absen')->nullable();
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['sesi_id', 'nim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
