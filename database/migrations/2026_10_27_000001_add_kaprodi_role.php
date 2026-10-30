<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM role agar mencakup 'kaprodi'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'mahasiswa', 'kaprodi') NOT NULL DEFAULT 'mahasiswa'");
    }

    public function down(): void
    {
        // Hapus user kaprodi dulu sebelum rollback enum
        DB::table('users')->where('role', 'kaprodi')->delete();
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa'");
    }
};
