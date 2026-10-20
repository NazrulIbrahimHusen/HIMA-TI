<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\PeriodeKepengurusan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Satu sumber perhitungan keaktifan untuk dashboard, monitoring admin, dan portal mahasiswa.
 *
 * Aturan:
 *  - Satu "unit" = satu anggota pada satu kegiatan (kegiatan published/closed, internal maupun kolaborasi).
 *  - Unit dihitung HADIR jika anggota hadir di SEMUA sesi kegiatan tersebut.
 *  - Persentase = unit hadir ÷ unit yang tercatat (hadir + tidak hadir) milik anggota itu sendiri.
 *  - Tamu tidak dihitung. Anggota yang belum absen baru tercatat "tidak hadir" setelah "Isi Alpa Otomatis".
 */
class MonitoringService
{
    public const GRADE = [
        'A' => ['min' => 80, 'label' => 'Sangat aktif',  'rentang' => '80–100%'],
        'B' => ['min' => 60, 'label' => 'Aktif',         'rentang' => '60–79%'],
        'C' => ['min' => 40, 'label' => 'Cukup aktif',   'rentang' => '40–59%'],
        'D' => ['min' => 20, 'label' => 'Kurang aktif',  'rentang' => '20–39%'],
        'E' => ['min' => 0,  'label' => 'Tidak aktif',   'rentang' => '0–19%'],
    ];

    public static function grade(float $persen): string
    {
        foreach (self::GRADE as $huruf => $d) {
            if ($persen >= $d['min']) {
                return $huruf;
            }
        }

        return 'E';
    }

    /** @return array{dari: ?Carbon, sampai: ?Carbon, label: string, kode: string} */
    public function rentang(?string $kode): array
    {
        $kode = $kode ?: 'semester';

        if ($kode === 'semua') {
            return ['dari' => null, 'sampai' => null, 'label' => 'semua waktu', 'kode' => 'semua'];
        }

        if (str_starts_with($kode, 'periode-')) {
            $periode = PeriodeKepengurusan::find((int) substr($kode, 8));
            if ($periode) {
                return [
                    'dari'   => Carbon::create($periode->tahun_mulai, 1, 1)->startOfDay(),
                    'sampai' => Carbon::create($periode->tahun_selesai, 12, 31)->endOfDay(),
                    'label'  => 'periode ' . $periode->nama,
                    'kode'   => $kode,
                ];
            }
        }

        $bulan = in_array($kode, ['3', '12'], true) ? (int) $kode : 6;

        return [
            'dari'   => now()->subMonths($bulan - 1)->startOfMonth(),
            'sampai' => now()->endOfMonth(),
            'label'  => $bulan === 6 ? '1 semester (6 bulan terakhir)' : "{$bulan} bulan terakhir",
            'kode'   => $bulan === 6 ? 'semester' : (string) $bulan,
        ];
    }

    /** Pilihan rentang untuk dropdown. */
    public function opsiRentang(): array
    {
        $opsi = [
            'semester' => '1 semester (6 bulan terakhir)',
            '3'        => '3 bulan terakhir',
            '12'       => '12 bulan terakhir',
            'semua'    => 'Semua waktu',
        ];

        foreach (PeriodeKepengurusan::orderByDesc('tahun_mulai')->get() as $p) {
            $opsi['periode-' . $p->id] = 'Periode ' . $p->nama . ($p->status === 'aktif' ? ' (aktif)' : '');
        }

        return $opsi;
    }

    /**
     * Unit kehadiran seluruh anggota pada rentang tanggal kegiatan.
     *
     * @return Collection<int, array{anggota_id:int, kegiatan_id:int, bulan:string, hadir:bool}>
     */
    public function unit(?Carbon $dari, ?Carbon $sampai): Collection
    {
        $sesiPerKegiatan = DB::table('sesi_absensi')
            ->groupBy('kegiatan_id')
            ->selectRaw('kegiatan_id, count(*) as total')
            ->pluck('total', 'kegiatan_id');

        return DB::table('absensi')
            ->join('sesi_absensi', 'sesi_absensi.id', '=', 'absensi.sesi_id')
            ->join('kegiatan', 'kegiatan.id', '=', 'sesi_absensi.kegiatan_id')
            ->whereNull('absensi.deleted_at')
            ->whereNull('kegiatan.deleted_at')
            ->whereIn('kegiatan.status', ['published', 'closed'])
            ->where('absensi.tipe_peserta', 'anggota')
            ->whereNotNull('absensi.anggota_id')
            ->when($dari, fn ($q) => $q->where('kegiatan.tanggal_mulai', '>=', $dari->toDateString()))
            ->when($sampai, fn ($q) => $q->where('kegiatan.tanggal_mulai', '<=', $sampai->toDateString()))
            ->groupBy('absensi.anggota_id', 'sesi_absensi.kegiatan_id')
            ->selectRaw("absensi.anggota_id, sesi_absensi.kegiatan_id, MAX(DATE_FORMAT(kegiatan.tanggal_mulai, '%Y-%m')) as bulan, SUM(absensi.status = 'hadir') as jumlah_hadir")
            ->get()
            ->map(fn ($u) => [
                'anggota_id'  => (int) $u->anggota_id,
                'kegiatan_id' => (int) $u->kegiatan_id,
                'bulan'       => $u->bulan,
                'hadir'       => (int) $u->jumlah_hadir >= (int) ($sesiPerKegiatan[$u->kegiatan_id] ?? 1),
            ]);
    }

    /** @return Collection<int, array{hadir:int, total:int, persen:float, grade:string}> keyed by anggota_id */
    public function perAnggota(Collection $units): Collection
    {
        return $units->groupBy('anggota_id')->map(function (Collection $u) {
            $total = $u->count();
            $hadir = $u->where('hadir', true)->count();
            $rasio = $hadir / $total * 100;

            return [
                'hadir'  => $hadir,
                'total'  => $total,
                'persen' => round($rasio, 1),
                'grade'  => self::grade($rasio),
            ];
        });
    }

    /**
     * Daftar monitoring seluruh anggota sesuai filter.
     *
     * @return array{rentang: array, baris: Collection, ringkasan: array}
     */
    public function daftar(?string $kodeRentang, array $filter = []): array
    {
        $rentang   = $this->rentang($kodeRentang);
        $statistik = $this->perAnggota($this->unit($rentang['dari'], $rentang['sampai']));
        $status    = $filter['status'] ?? 'aktif';

        $anggota = Anggota::query()
            ->when(in_array($status, ['aktif', 'tidak_aktif'], true), fn ($q) => $q->where('status', $status))
            ->when(!empty($filter['prodi']), fn ($q) => $q->where('prodi', $filter['prodi']))
            ->when(!empty($filter['angkatan']), fn ($q) => $q->where('angkatan', $filter['angkatan']))
            ->when(!empty($filter['q']), fn ($q) => $q->search($filter['q']))
            ->get();

        $baris = $anggota->map(function (Anggota $a) use ($statistik) {
            $s = $statistik->get($a->id);

            return [
                'anggota' => $a,
                'hadir'   => $s['hadir'] ?? 0,
                'total'   => $s['total'] ?? 0,
                'persen'  => $s['persen'] ?? null,
                'grade'   => $s['grade'] ?? null,
            ];
        })->values();

        $dinilai    = $baris->whereNotNull('grade');
        $distribusi = array_fill_keys(array_keys(self::GRADE), 0);
        foreach ($dinilai as $b) {
            $distribusi[$b['grade']]++;
        }

        return [
            'rentang'   => $rentang,
            'baris'     => $baris,
            'ringkasan' => [
                'jumlah'     => $baris->count(),
                'dinilai'    => $dinilai->count(),
                'belum'      => $baris->count() - $dinilai->count(),
                'rata'       => $dinilai->isEmpty() ? null : round($dinilai->avg('persen'), 1),
                'distribusi' => $distribusi,
            ],
        ];
    }

    /** Riwayat kehadiran satu anggota per kegiatan. */
    public function riwayat(Anggota $anggota, ?Carbon $dari, ?Carbon $sampai): array
    {
        $rows = Absensi::with('sesi')
            ->where('anggota_id', $anggota->id)
            ->where('tipe_peserta', 'anggota')
            ->whereHas('sesi.kegiatan', function ($q) use ($dari, $sampai) {
                $q->whereIn('status', ['published', 'closed'])
                    ->when($dari, fn ($k) => $k->where('tanggal_mulai', '>=', $dari->toDateString()))
                    ->when($sampai, fn ($k) => $k->where('tanggal_mulai', '<=', $sampai->toDateString()));
            })
            ->get();

        $kegiatan = Kegiatan::withCount('sesiAbsensi')
            ->whereIn('id', $rows->pluck('sesi.kegiatan_id')->unique()->values())
            ->get()
            ->keyBy('id');

        $baris = $rows
            ->groupBy(fn ($a) => $a->sesi->kegiatan_id)
            ->filter(fn ($items, $kegiatanId) => $kegiatan->has($kegiatanId))
            ->map(function (Collection $items, $kegiatanId) use ($kegiatan) {
                $k = $kegiatan[$kegiatanId];

                return [
                    'kegiatan' => $k,
                    'status'   => $items->sortBy(fn ($a) => $a->sesi->waktu_mulai)
                        ->map(fn ($a) => ['sesi' => $a->sesi->nama_sesi, 'status' => $a->status])->values(),
                    'hadir'    => $items->where('status', 'hadir')->count() >= max((int) $k->sesi_absensi_count, 1),
                ];
            })
            ->sortByDesc(fn ($b) => $b['kegiatan']->tanggal_mulai)
            ->values();

        $total = $baris->count();
        $hadir = $baris->where('hadir', true)->count();

        return [
            'baris'  => $baris,
            'hadir'  => $hadir,
            'total'  => $total,
            'persen' => $total ? round($hadir / $total * 100, 1) : null,
            'grade'  => $total ? self::grade($hadir / $total * 100) : null,
        ];
    }
}
