<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Services\MonitoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private MonitoringService $monitoring)
    {
    }

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

        $bulanKeys   = [];
        $bulanLabels = [];
        for ($i = 0; $i < $bulan; $i++) {
            $m             = $start->copy()->addMonths($i);
            $bulanKeys[]   = $m->format('Y-m');
            $bulanLabels[] = $m->translatedFormat('M Y');
        }

        // Kegiatan per bulan
        $kegiatan = Kegiatan::whereBetween('tanggal_mulai', $range)->get();
        $kegiatanPerBulan = collect($bulanKeys)->map(
            fn ($key) => $kegiatan->filter(fn ($k) => $k->tanggal_mulai->format('Y-m') === $key)->count()
        )->all();

        // Total kehadiran (anggota + tamu) pada kegiatan dalam periode
        $totalHadir = Absensi::where('status', 'hadir')
            ->whereHas('sesi.kegiatan', fn ($q) => $q->whereBetween('tanggal_mulai', $range))
            ->count();

        // Keaktifan: aturan yang sama dengan halaman Monitoring
        $units         = $this->monitoring->unit($start, $end);
        $rataKeaktifan = $units->isEmpty() ? null : round($units->where('hadir', true)->count() / $units->count() * 100, 1);

        $trenKeaktifan = collect($bulanKeys)->map(function ($key) use ($units) {
            $bulanIni = $units->where('bulan', $key);

            return $bulanIni->isEmpty()
                ? null
                : round($bulanIni->where('hadir', true)->count() / $bulanIni->count() * 100, 1);
        })->all();

        $grade = array_fill_keys(array_keys(MonitoringService::GRADE), 0);
        foreach ($this->monitoring->perAnggota($units) as $s) {
            $grade[$s['grade']]++;
        }

        return view('admin.dashboard', [
            'bulan'            => $bulan,
            'bulanLabels'      => $bulanLabels,
            'kegiatanPerBulan' => $kegiatanPerBulan,
            'trenKeaktifan'    => $trenKeaktifan,
            'grade'            => $grade,
            'anggotaAktif'     => Anggota::aktif()->count(),
            'totalKegiatan'    => $kegiatan->count(),
            'totalHadir'       => $totalHadir,
            'rataKeaktifan'    => $rataKeaktifan,
            'absensiTerbaru'   => Absensi::with('sesi.kegiatan')->latest()->limit(8)->get(),
        ]);
    }
}
