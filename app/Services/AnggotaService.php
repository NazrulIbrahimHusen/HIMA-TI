<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnggotaService
{
    public const HASIL_DIBUAT      = 'dibuat';
    public const HASIL_DIPULIHKAN  = 'dipulihkan';

    /**
     * Buat anggota baru beserta akun login mahasiswa (password default = NIM).
     * Jika NIM pernah terdaftar lalu dihapus (soft delete), data dipulihkan.
     *
     * @return array{0: Anggota, 1: string}
     */
    public function simpan(array $data, ?UploadedFile $foto = null): array
    {
        return DB::transaction(function () use ($data, $foto) {
            $nim = (string) $data['nim'];

            $user    = User::withTrashed()->where('nim', $nim)->first();
            $anggota = Anggota::withTrashed()->where('nim', $nim)->first();

            $dipulihkan = ($user && $user->trashed()) || ($anggota && $anggota->trashed());

            // Akun login
            if ($user && $user->trashed()) {
                $user->restore();
            }
            $user ??= new User();
            $user->forceFill([
                'name'             => $data['nama'],
                'nim'              => $nim,
                'password'         => $nim, // di-hash otomatis oleh cast 'hashed'
                'role'             => 'mahasiswa',
                'password_changed' => false,
            ])->save();
            $user->syncRoles('mahasiswa');

            // Profil anggota
            if ($anggota && $anggota->trashed()) {
                $anggota->restore();
            }
            $anggota ??= new Anggota();
            $anggota->fill([
                'user_id'  => $user->id,
                'nim'      => $nim,
                'nama'     => $data['nama'],
                'prodi'    => $data['prodi'],
                'angkatan' => $data['angkatan'],
                'semester' => $data['semester'],
                'status'   => $data['status'] ?? 'aktif',
            ]);

            if ($foto) {
                $this->hapusFoto($anggota->foto);
                $anggota->foto = $foto->store('foto-anggota', 'public');
            }

            $anggota->save();

            return [$anggota, $dipulihkan ? self::HASIL_DIPULIHKAN : self::HASIL_DIBUAT];
        });
    }

    /** Ubah data anggota. NIM tidak diubah karena dipakai untuk login. */
    public function perbarui(Anggota $anggota, array $data, ?UploadedFile $foto = null): Anggota
    {
        return DB::transaction(function () use ($anggota, $data, $foto) {
            $anggota->fill([
                'nama'     => $data['nama'],
                'prodi'    => $data['prodi'],
                'angkatan' => $data['angkatan'],
                'semester' => $data['semester'],
                'status'   => $data['status'],
            ]);

            if ($foto) {
                $this->hapusFoto($anggota->foto);
                $anggota->foto = $foto->store('foto-anggota', 'public');
            }

            $anggota->save();
            $anggota->user?->update(['name' => $data['nama']]);

            return $anggota;
        });
    }

    /** Soft delete anggota + akun + data absensinya (cascade). */
    public function hapus(Anggota $anggota): void
    {
        if ($anggota->pengurus()->exists()) {
            throw new DomainException(
                'Anggota tidak bisa dihapus karena tercatat dalam struktur pengurus. '
                . 'Ubah statusnya menjadi tidak aktif, atau hapus dari struktur pengurus terlebih dahulu.'
            );
        }

        DB::transaction(function () use ($anggota) {
            $anggota->absensi()->delete();
            $anggota->user?->delete();
            $anggota->delete();
        });
    }

    /** Reset password ke NIM dan wajibkan ganti saat login berikutnya. */
    public function resetPassword(Anggota $anggota): void
    {
        $anggota->user?->update([
            'password'         => $anggota->nim,
            'password_changed' => false,
        ]);
    }

    private function hapusFoto(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
