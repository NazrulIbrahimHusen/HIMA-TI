<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Services\MonitoringService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MonitoringController extends Controller
{
    private const PER_HALAMAN = 25;

    public function __construct(private MonitoringService $service)
    {
    }

    public function index(Request $request)
    {
        $hasil = $this->service->daftar($request->query('rentang'), $request->only(['q', 'prodi', 'angkatan', 'status']));

        $baris = $hasil['baris'];
        if ($request->filled('grade')) {
            $baris = $request->grade === '-'
                ? $baris->whereNull('grade')
                : $baris->where('grade', $request->grade);
        }

        $baris = $this->paginasi($this->urutkan($baris, (string) $request->query('urut', 'persen_desc')), $request);
        $kode  = $hasil['rentang']['kode'];

        // Search real-time (AJAX) hanya butuh tabelnya
        if ($request->ajax()) {
            return view('admin.monitoring._tabel', compact('baris', 'kode'));
        }

        return view('admin.monitoring.index', [
            'baris'        => $baris,
            'kode'         => $kode,
            'label'        => $hasil['rentang']['label'],
            'ringkasan'    => $hasil['ringkasan'],
            'opsiRentang'  => $this->service->opsiRentang(),
            'grade'        => MonitoringService::GRADE,
            'prodiList'    => Anggota::query()->select('prodi')->distinct()->orderBy('prodi')->pluck('prodi'),
            'angkatanList' => Anggota::query()->select('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
        ]);
    }

    public function anggota(Request $request, Anggota $anggota)
    {
        $rentang = $this->service->rentang($request->query('rentang'));

        return view('admin.monitoring.anggota', [
            'anggota'     => $anggota,
            'riwayat'     => $this->service->riwayat($anggota, $rentang['dari'], $rentang['sampai']),
            'kode'        => $rentang['kode'],
            'label'       => $rentang['label'],
            'opsiRentang' => $this->service->opsiRentang(),
        ]);
    }

    private function urutkan(Collection $baris, string $urut): Collection
    {
        $nama = fn (array $b) => mb_strtolower($b['anggota']->nama);

        return match ($urut) {
            'persen_asc' => $baris->sort(fn ($a, $b) => [$a['persen'] ?? 101, $nama($a)] <=> [$b['persen'] ?? 101, $nama($b)]),
            'nama'       => $baris->sort(fn ($a, $b) => $nama($a) <=> $nama($b)),
            default      => $baris->sort(fn ($a, $b) => [$b['persen'] ?? -1, $b['total'], $nama($a)] <=> [$a['persen'] ?? -1, $a['total'], $nama($b)]),
        };
    }

    private function paginasi(Collection $koleksi, Request $request): LengthAwarePaginator
    {
        $halaman = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $koleksi->values()->forPage($halaman, self::PER_HALAMAN)->values(),
            $koleksi->count(),
            self::PER_HALAMAN,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
