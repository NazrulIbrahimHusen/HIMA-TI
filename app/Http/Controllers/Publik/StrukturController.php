<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\PeriodeKepengurusan;
use App\Models\Pengurus;
use Illuminate\Http\Request;

class StrukturController extends Controller
{
    public function index(Request $request)
    {
        $daftarPeriode = PeriodeKepengurusan::orderByDesc('tahun_mulai')->orderByDesc('id')->get();

        $periode = $daftarPeriode->firstWhere('id', (int) $request->query('periode'))
            ?? $daftarPeriode->firstWhere('status', 'aktif')
            ?? $daftarPeriode->first();

        $inti   = collect();
        $divisi = collect();

        if ($periode) {
            $inti = Pengurus::with('anggota')
                ->where('periode_id', $periode->id)
                ->whereNull('divisi_id')
                ->orderBy('urutan')->orderBy('id')
                ->get();

            $divisi = Divisi::where('periode_id', $periode->id)
                ->with(['pengurus' => fn ($q) => $q->with('anggota')->orderBy('urutan')->orderBy('id')])
                ->orderBy('urutan')->orderBy('id')
                ->get();
        }

        return view('publik.struktur', compact('daftarPeriode', 'periode', 'inti', 'divisi'));
    }
}
