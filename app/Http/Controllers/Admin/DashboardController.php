<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\SesiAbsensi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        // Periode statistik: default 6 bulan (1 semester), bisa diganti 3 / 12 bulan
        $bulan = (int) $request->query('periode', 6);
        if (!in_array($bulan, [3, 6, 12], true)) {
            $bulan = 6;
        }

        $start = now()->subMonths($bulan - 1)->startOfMonth();
        $end   = now()->endOfMonth();
        $range = [$start->toDateString(), $end->toDateString()];

        // Label bulan untuk sumbu X grafik
        $bulanKeys   = [];
        $bulanLabels = [];
        for ($i = 0; $i < $bulan; $i++) {
            $m             = $start->copy()->addMonths($i);
            $bulanKeys[]   = $m->format('Y-m');
            $bulanLabels[] = $m->translatedFormat('M Y');
        }

        // ── Kegiatan dalam periode ──────────────────────────────────────────
        $kegiatan = Kegiatan::whereBetween('tanggal_mulai', $range)->get();
        $kegiatanPerBulan = collect($bulanKeys)->map(
            fn ($key) => $kegiatan->filter(fn ($k) => $k->tanggal_mulai->format('Y-m') === $key)->count()
        )->all();

        // ── Absensi dalam periode ───────────────────────────────────────────
        $absensi = Absensi::with('sesi.kegiatan')
            ->whereHas('sesi.kegiatan', fn ($q) => $q->whereBetween('tanggal_mulai', $range))
            ->get();

        $totalHadir = $absensi->where('status', 'hadir')->count();

        // Jumlah sesi per kegiatan (untuk aturan: harus hadir di SEMUA sesi)
        $sesiCount = SesiAbsensi::selectRaw('kegiatan_id, count(*) as total')
            ->groupBy('kegiatan_id')
            ->pluck('total', 'kegiatan_id');

        // Satu "unit" = satu anggota pada satu kegiatan. Tamu tidak dihitung.
        $units = collect();
        $rowsAnggota = $absensi->where('tipe_peserta', 'anggota')->whereNotNull('anggota_id');

        foreach ($rowsAnggota->groupBy('anggota_id') as $anggotaId => $perAnggota) {
            foreach ($perAnggota->groupBy(fn ($a) => $a->sesi->kegiatan_id) as $kegiatanId => $items) {
                $units->push([
                    'anggota_id' => $anggotaId,
                    'month'      => $items->first()->sesi->kegiatan->tanggal_mulai->format('Y-m'),
                    'hadir'      => $items->where('status', 'hadir')->count() >= ($sesiCount[$kegiatanId] ?? 1),
                ]);
            }
        }

        // Rata-rata keaktifan keseluruhan
        $rataKeaktifan = $units->isEmpty()
            ? null
            : round($units->where('hadir', true)->count() / $units->count() * 100, 1);

        // Tren keaktifan per bulan (null jika belum ada data bulan itu)
        $trenKeaktifan = collect($bulanKeys)->map(function ($key) use ($units) {
            $bulanIni = $units->where('month', $key);
            return $bulanIni->isEmpty()
                ? null
                : round($bulanIni->where('hadir', true)->count() / $bulanIni->count() * 100, 1);
        })->all();

        // Distribusi grade A–E per anggota
        $grade = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
        foreach ($units->groupBy('anggota_id') as $perAnggota) {
            $persen = $perAnggota->where('hadir', true)->count() / $perAnggota->count() * 100;
            $grade[$this->hitungGrade($persen)]++;
        }

        // ── Ringkasan & absensi terbaru ─────────────────────────────────────
        $anggotaAktif  = Anggota::aktif()->count();
        $absensiTerbaru = Absensi::with('sesi.kegiatan')->latest()->limit(8)->get();

        return view('admin.dashboard', [
            'bulan'            => $bulan,
            'bulanLabels'      => $bulanLabels,
            'kegiatanPerBulan' => $kegiatanPerBulan,
            'trenKeaktifan'    => $trenKeaktifan,
            'grade'            => $grade,
            'anggotaAktif'     => $anggotaAktif,
            'totalKegiatan'    => $kegiatan->count(),
            'totalHadir'       => $totalHadir,
            'rataKeaktifan'    => $rataKeaktifan,
            'absensiTerbaru'   => $absensiTerbaru,
        ]);
    }

    private function hitungGrade(float $persen): string
    {
        return match (true) {
            $persen >= 80 => 'A',
            $persen >= 60 => 'B',
            $persen >= 40 => 'C',
            $persen >= 20 => 'D',
            default       => 'E',
        };
    }
}
