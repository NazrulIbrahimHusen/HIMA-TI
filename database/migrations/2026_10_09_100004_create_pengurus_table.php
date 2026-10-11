<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periode_kepengurusan')->onDelete('cascade');
            $table->foreignId('divisi_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->string('jabatan', 100);
            $table->tinyInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengurus');
    }
};
