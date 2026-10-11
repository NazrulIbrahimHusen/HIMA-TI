<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\AbsensiLog;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AbsensiService
{
    /**
     * Rekap per peserta (unik per NIM) dengan status di tiap sesi.
     * Peserta dihitung "hadir" hanya jika hadir di SEMUA sesi kegiatan.
     *
     * @return array{sesi: Collection, baris: Collection}
     */
    public function matriks(Kegiatan $kegiatan): array
    {
        $sesi       = $kegiatan->sesiAbsensi()->orderBy('waktu_mulai')->get();
        $jumlahSesi = $sesi->count();

        $rows = Absensi::whereIn('sesi_id', $sesi->pluck('id'))
            ->get(['id', 'sesi_id', 'nim', 'nama', 'tipe_peserta', 'status']);

        $baris = $rows->groupBy('nim')->map(function (Collection $items, $nim) use ($jumlahSesi) {
            $status  = $items->pluck('status', 'sesi_id')->all();
            $hadir   = count(array_filter($status, fn ($s) => $s === 'hadir'));
            $pertama = $items->first();

            return [
                'nim'          => (string) $nim,
                'nama'         => $pertama->nama,
                'tipe'         => $pertama->tipe_peserta,
                'status'       => $status,
                'jumlah_hadir' => $hadir,
                'hadir_semua'  => $jumlahSesi > 0 && $hadir >= $jumlahSesi,
            ];
        })->values()->sortBy(fn ($b) => mb_strtolower($b['nama']))->values();

        return ['sesi' => $sesi, 'baris' => $baris];
    }

    public function saring(Collection $baris, ?string $q, ?string $tipe): Collection
    {
        $q = $q ? mb_strtolower(trim($q)) : null;

        return $baris
            ->when($q, fn ($c) => $c->filter(
                fn ($b) => str_contains(mb_strtolower($b['nama']), $q) || str_contains($b['nim'], $q)
            ))
            ->when(in_array($tipe, ['anggota', 'tamu'], true), fn ($c) => $c->where('tipe', $tipe))
            ->values();
    }

    public function ringkasan(Collection $baris): array
    {
        $hadir = $baris->where('hadir_semua', true)->count();

        return [
            'total'       => $baris->count(),
            'hadir'       => $hadir,
            'tidak_hadir' => $baris->count() - $hadir,
            'tamu'        => $baris->where('tipe', 'tamu')->count(),
        ];
    }

    /**
     * Catat absensi manual oleh admin untuk satu sesi atau semua sesi.
     *
     * @return array{0:int, 1:int, 2:string} [dibuat, diubah, nama]
     */
    public function simpanManual(Kegiatan $kegiatan, array $data, User $admin): array
    {
        $sesiDipilih = $kegiatan->sesiAbsensi()->orderBy('waktu_mulai')->get();
        if ($data['sesi_id'] !== 'semua') {
            $sesiDipilih = $sesiDipilih->where('id', (int) $data['sesi_id']);
        }

        $nim     = (string) $data['nim'];
        $anggota = Anggota::where('nim', $nim)->first();
        $nama    = $anggota?->nama ?? trim((string) ($data['nama'] ?? ''));

        if ($nama === '') {
            throw new DomainException('NIM belum terdaftar sebagai anggota. Isi nama untuk mencatat sebagai tamu.');
        }

        $status     = $data['status'];
        $keterangan = $data['keterangan'] ?? null;
        $dibuat     = 0;
        $diubah     = 0;

        DB::transaction(function () use ($sesiDipilih, $nim, $anggota, $nama, $status, $keterangan, $admin, &$dibuat, &$diubah) {
            foreach ($sesiDipilih as $sesi) {
                $row = Absensi::withTrashed()->where('sesi_id', $sesi->id)->where('nim', $nim)->first();

                $atribut = [
                    'sesi_id'      => $sesi->id,
                    'anggota_id'   => $anggota?->id,
                    'nim'          => $nim,
                    'nama'         => $nama,
                    'tipe_peserta' => $anggota ? 'anggota' : 'tamu',
                    'status'       => $status,
                    'metode'       => 'manual',
                    'waktu_absen'  => $status === 'hadir' ? now() : null,
                    'keterangan'   => $keterangan,
                ];

                if ($row && !$row->trashed()) {
                    if ($row->status === $status) {
                        continue;
                    }

                    AbsensiLog::create([
                        'absensi_id'  => $row->id,
                        'changed_by'  => $admin->id,
                        'status_lama' => $row->status,
                        'status_baru' => $status,
                        'catatan'     => $keterangan,
                    ]);

                    $row->update([
                        'status'      => $status,
                        'keterangan'  => $keterangan ?: $row->keterangan,
                        'waktu_absen' => ($status === 'hadir' && !$row->waktu_absen) ? now() : $row->waktu_absen,
                    ]);
                    $diubah++;
                } elseif ($row) {
                    $row->restore();
                    $row->update($atribut);
                    $dibuat++;
                } else {
                    Absensi::create($atribut);
                    $dibuat++;
                }
            }
        });

        return [$dibuat, $diubah, $nama];
    }

    /**
     * Ubah status satu peserta di beberapa sesi sekaligus. Setiap perubahan dicatat di absensi_log.
     *
     * @param array<int|string, ?string> $statusPerSesi sesi_id => status baru ('' = tidak diubah)
     */
    public function perbaruiPeserta(Kegiatan $kegiatan, string $nim, array $statusPerSesi, ?string $keterangan, User $admin): int
    {
        $sesi = $kegiatan->sesiAbsensi()->get()->keyBy('id');
        $ref  = Absensi::whereIn('sesi_id', $sesi->keys())->where('nim', $nim)->first();

        if (!$ref) {
            throw new DomainException('Peserta tidak ditemukan pada kegiatan ini.');
        }

        $diubah = 0;

        DB::transaction(function () use ($sesi, $nim, $statusPerSesi, $keterangan, $admin, $ref, &$diubah) {
            foreach ($statusPerSesi as $sesiId => $statusBaru) {
                if (!$statusBaru || !$sesi->has((int) $sesiId)) {
                    continue;
                }

                $row = Absensi::withTrashed()->where('sesi_id', (int) $sesiId)->where('nim', $nim)->first();

                if ($row && !$row->trashed()) {
                    if ($row->status === $statusBaru) {
                        continue;
                    }

                    AbsensiLog::create([
                        'absensi_id'  => $row->id,
                        'changed_by'  => $admin->id,
                        'status_lama' => $row->status,
                        'status_baru' => $statusBaru,
                        'catatan'     => $keterangan,
                    ]);

                    $row->update([
                        'status'      => $statusBaru,
                        'keterangan'  => $keterangan ?: $row->keterangan,
                        'waktu_absen' => ($statusBaru === 'hadir' && !$row->waktu_absen) ? now() : $row->waktu_absen,
                    ]);
                } else {
                    $atribut = [
                        'sesi_id'      => (int) $sesiId,
                        'anggota_id'   => $ref->anggota_id,
                        'nim'          => $nim,
                        'nama'         => $ref->nama,
                        'tipe_peserta' => $ref->tipe_peserta,
                        'status'       => $statusBaru,
                        'metode'       => 'manual',
                        'waktu_absen'  => $statusBaru === 'hadir' ? now() : null,
                        'keterangan'   => $keterangan,
                    ];

                    if ($row) {
                        $row->restore();
                        $row->update($atribut);
                    } else {
                        Absensi::create($atribut);
                    }
                }

                $diubah++;
            }
        });

        return $diubah;
    }

    /** Soft delete semua data absensi satu peserta pada kegiatan ini. */
    public function hapusPeserta(Kegiatan $kegiatan, string $nim): int
    {
        return Absensi::whereIn('sesi_id', $kegiatan->sesiAbsensi()->pluck('id'))
            ->where('nim', $nim)
            ->delete();
    }

    /**
     * Catat "alpa" untuk anggota aktif yang belum punya data absensi
     * pada sesi yang sudah berakhir. Dibutuhkan agar monitoring bisa menghitung "tidak hadir".
     */
    public function isiAlpa(Kegiatan $kegiatan): int
    {
        $sesiSelesai = $kegiatan->sesiAbsensi()->where('waktu_selesai', '<', now())->get();
        if ($sesiSelesai->isEmpty()) {
            return 0;
        }

        $anggota = Anggota::aktif()->get(['id', 'nim', 'nama']);
        $total   = 0;
        $waktu   = now();

        DB::transaction(function () use ($sesiSelesai, $anggota, $waktu, &$total) {
            foreach ($sesiSelesai as $sesi) {
                $sudahAda = Absensi::withTrashed()->where('sesi_id', $sesi->id)->pluck('nim')
                    ->map(fn ($n) => (string) $n)->flip();

                $baru = $anggota
                    ->reject(fn ($a) => $sudahAda->has((string) $a->nim))
                    ->map(fn ($a) => [
                        'sesi_id'      => $sesi->id,
                        'anggota_id'   => $a->id,
                        'nim'          => (string) $a->nim,
                        'nama'         => $a->nama,
                        'tipe_peserta' => 'anggota',
                        'status'       => 'alpa',
                        'metode'       => 'manual',
                        'waktu_absen'  => null,
                        'keterangan'   => null,
                        'created_at'   => $waktu,
                        'updated_at'   => $waktu,
                        'deleted_at'   => null,
                    ])->values()->all();

                foreach (array_chunk($baru, 500) as $potongan) {
                    Absensi::insert($potongan);
                }

                $total += count($baru);
            }
        });

        return $total;
    }
}
