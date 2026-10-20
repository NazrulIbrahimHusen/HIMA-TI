<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Services\SertifikatService;

class UnduhSertifikatController extends Controller
{
    public function __construct(private SertifikatService $sertifikat)
    {
    }

    /** Validasi login + kehadiran di server, baru arahkan ke link sertifikat (link tidak pernah tampil di halaman). */
    public function unduh(Kegiatan $kegiatan)
    {
        abort_unless(in_array($kegiatan->status, ['published', 'closed'], true), 404);

        $status = $this->sertifikat->status(auth()->user(), $kegiatan);

        if ($status !== 'siap') {
            return redirect()->route('publik.kegiatan.show', $kegiatan)->with('error', $this->sertifikat->pesan($status));
        }

        return redirect()->away($kegiatan->sertifikat_url);
    }
}
