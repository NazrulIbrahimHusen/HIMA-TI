<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Services\SertifikatService;
use Illuminate\Http\Request;

class KegiatanPublikController extends Controller
{
    public function __construct(private SertifikatService $sertifikat)
    {
    }

    public function index(Request $request)
    {
        $hariIni = now()->toDateString();

        $kegiatan = Kegiatan::publik()
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->status === 'mendatang', fn ($q) => $q->where('status', 'published')->where('tanggal_selesai', '>=', $hariIni))
            ->when($request->status === 'selesai', fn ($q) => $q->where(
                fn ($w) => $w->where('status', 'closed')->orWhere('tanggal_selesai', '<', $hariIni)
            ))
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        if ($request->ajax()) {
            return view('publik.kegiatan._daftar', compact('kegiatan'));
        }

        return view('publik.kegiatan.index', compact('kegiatan'));
    }

    public function show(Kegiatan $kegiatan)
    {
        abort_unless(in_array($kegiatan->status, ['published', 'closed'], true), 404);

        $kegiatan->load(['sesiAbsensi' => fn ($q) => $q->orderBy('waktu_mulai')]);

        $statusSertifikat = $this->sertifikat->status(auth()->user(), $kegiatan);

        return view('publik.kegiatan.show', [
            'kegiatan'         => $kegiatan,
            'statusSertifikat' => $statusSertifikat,
            'pesanSertifikat'  => $this->sertifikat->pesan($statusSertifikat),
        ]);
    }
}
