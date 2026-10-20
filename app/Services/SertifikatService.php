<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\User;

/**
 * Aturan akses sertifikat:
 *  - sertifikat diaktifkan admin dan link tersedia,
 *  - pengguna login sebagai mahasiswa yang terdaftar sebagai anggota,
 *  - anggota tercatat HADIR di semua sesi kegiatan.
 * Link asli tidak pernah dikirim ke browser; unduhan lewat redirect setelah semua syarat terpenuhi.
 */
class SertifikatService
{
    public const PESAN = [
        'belum_tersedia'  => 'Sertifikat untuk kegiatan ini belum tersedia.',
        'belum_login'     => 'Masuk sebagai mahasiswa untuk mengunduh sertifikat.',
        'bukan_mahasiswa' => 'Akun ini bukan akun mahasiswa, sehingga tidak dapat mengunduh sertifikat.',
        'tidak_hadir'     => 'Sertifikat hanya untuk peserta yang tercatat hadir di seluruh sesi kegiatan ini.',
        'siap'            => '',
    ];

    /** @return 'belum_tersedia'|'belum_login'|'bukan_mahasiswa'|'tidak_hadir'|'siap' */
    public function status(?User $user, Kegiatan $kegiatan): string
    {
        if (!$kegiatan->sertifikat_aktif || blank($kegiatan->sertifikat_url)) {
            return 'belum_tersedia';
        }

        if (!$user) {
            return 'belum_login';
        }

        $anggota = $user->isMahasiswa() ? $user->anggota : null;
        if (!$anggota) {
            return 'bukan_mahasiswa';
        }

        return $this->hadirDiSemuaSesi($anggota, $kegiatan) ? 'siap' : 'tidak_hadir';
    }

    public function pesan(string $status): string
    {
        return self::PESAN[$status] ?? '';
    }

    private function hadirDiSemuaSesi(Anggota $anggota, Kegiatan $kegiatan): bool
    {
        $jumlahSesi = $kegiatan->sesiAbsensi()->count();
        if ($jumlahSesi === 0) {
            return false;
        }

        $hadir = Absensi::where('anggota_id', $anggota->id)
            ->where('status', 'hadir')
            ->whereIn('sesi_id', $kegiatan->sesiAbsensi()->select('id'))
            ->count();

        return $hadir >= $jumlahSesi;
    }
}
