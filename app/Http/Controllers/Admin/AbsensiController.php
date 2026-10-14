<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AbsensiRekapExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiLog;
use App\Models\Kegiatan;
use App\Services\AbsensiService;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    private const PER_HALAMAN = 25;

    public function __construct(private AbsensiService $service)
    {
    }

    /** Daftar kegiatan → pilih kegiatan untuk dikelola absensinya. */
    public function index(Request $request)
    {
        $kegiatan = Kegiatan::query()
            ->withCount('sesiAbsensi')
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        $peserta = DB::table('absensi')
            ->join('sesi_absensi', 'sesi_absensi.id', '=', 'absensi.sesi_id')
            ->whereNull('absensi.deleted_at')
            ->whereIn('sesi_absensi.kegiatan_id', $kegiatan->pluck('id'))
            ->groupBy('sesi_absensi.kegiatan_id')
            ->selectRaw('sesi_absensi.kegiatan_id as kegiatan_id, count(distinct absensi.nim) as total')
            ->pluck('total', 'kegiatan_id');

        if ($request->ajax()) {
            return view('admin.absensi._daftar', compact('kegiatan', 'peserta'));
        }

        return view('admin.absensi.index', compact('kegiatan', 'peserta'));
    }

    /** Rekap absensi satu kegiatan: peserta × sesi. */
    public function kegiatan(Request $request, Kegiatan $kegiatan)
    {
        $rekap  = $this->service->matriks($kegiatan);
        $sesi   = $rekap['sesi'];
        $semua  = $rekap['baris'];

        $disaring = $this->service->saring($semua, $request->query('q'), $request->query('tipe'));
        $baris    = $this->paginasi($disaring, $request);

        if ($request->ajax()) {
            return view('admin.absensi._rekap', compact('kegiatan', 'sesi', 'baris'));
        }

        return view('admin.absensi.kegiatan', [
            'kegiatan'  => $kegiatan,
            'sesi'      => $sesi,
            'baris'     => $baris,
            'ringkasan' => $this->service->ringkasan($semua),
            'sesiSelesai' => $sesi->filter(fn ($s) => $s->isExpired())->count(),
        ]);
    }

    public function tambah(Request $request, Kegiatan $kegiatan)
    {
        $idSesi = $kegiatan->sesiAbsensi()->pluck('id')->map(fn ($id) => (string) $id)->all();

        $data = $request->validate([
            'nim'        => ['required', 'digits_between:1,10'],
            'nama'       => ['nullable', 'string', 'max:255'],
            'sesi_id'    => ['required', Rule::in(array_merge(['semua'], $idSesi))],
            'status'     => ['required', 'in:hadir,izin,sakit,alpa'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'nim.required'       => 'NIM wajib diisi.',
            'nim.digits_between' => 'NIM harus berupa angka, maksimal 10 digit.',
            'sesi_id.required'   => 'Pilih sesi.',
            'sesi_id.in'         => 'Sesi tidak valid.',
            'status.required'    => 'Pilih status.',
        ]);

        try {
            [$dibuat, $diubah, $nama] = $this->service->simpanManual($kegiatan, $data, $request->user());
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['nim' => $e->getMessage()]);
        }

        if ($dibuat + $diubah === 0) {
            return back()->with('success', "Tidak ada perubahan: {$nama} sudah memiliki status yang sama.");
        }

        return back()->with('success', "Absensi {$nama} dicatat: {$dibuat} baru, {$diubah} diperbarui.");
    }

    public function peserta(Kegiatan $kegiatan, string $nim)
    {
        $sesi    = $kegiatan->sesiAbsensi()->orderBy('waktu_mulai')->get();
        $dataRow = Absensi::withTrashed()->with('sesi')->whereIn('sesi_id', $sesi->pluck('id'))->where('nim', $nim)->get();

        $aktif = $dataRow->whereNull('deleted_at')->keyBy('sesi_id');
        abort_if($aktif->isEmpty(), 404);

        $ref = $aktif->first();

        $log = AbsensiLog::with('changedBy')
            ->whereIn('absensi_id', $dataRow->pluck('id'))
            ->orderByDesc('id')
            ->get();

        return view('admin.absensi.peserta', [
            'kegiatan' => $kegiatan,
            'sesi'     => $sesi,
            'nim'      => $nim,
            'nama'     => $ref->nama,
            'tipe'     => $ref->tipe_peserta,
            'aktif'    => $aktif,
            'log'      => $log,
            'sesiPerRow' => $dataRow->keyBy('id'),
        ]);
    }

    public function perbaruiPeserta(Request $request, Kegiatan $kegiatan, string $nim)
    {
        $data = $request->validate([
            'status'     => ['nullable', 'array'],
            'status.*'   => ['nullable', 'in:hadir,izin,sakit,alpa'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $diubah = $this->service->perbaruiPeserta(
                $kegiatan, $nim, $data['status'] ?? [], $data['keterangan'] ?? null, $request->user()
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $diubah > 0 ? "{$diubah} status absensi diperbarui dan dicatat di riwayat." : 'Tidak ada perubahan.');
    }

    public function hapusPeserta(Kegiatan $kegiatan, string $nim)
    {
        $jumlah = $this->service->hapusPeserta($kegiatan, $nim);

        return redirect()->route('admin.absensi.kegiatan', $kegiatan)
            ->with('success', "{$jumlah} data absensi peserta {$nim} dihapus.");
    }

    public function isiAlpa(Kegiatan $kegiatan)
    {
        $jumlah = $this->service->isiAlpa($kegiatan);

        if ($jumlah === 0) {
            return back()->with('success', 'Tidak ada data baru. Semua anggota aktif sudah tercatat, atau belum ada sesi yang berakhir.');
        }

        return back()->with('success', "{$jumlah} data Alpa dibuat untuk anggota aktif yang belum absen pada sesi yang sudah berakhir.");
    }

    public function exportExcel(Kegiatan $kegiatan)
    {
        $rekap = $this->service->matriks($kegiatan);
        $nama  = 'absensi-' . Str::slug($kegiatan->nama) . '-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new AbsensiRekapExport($kegiatan, $rekap['sesi'], $rekap['baris']), $nama);
    }

    public function exportPdf(Kegiatan $kegiatan)
    {
        $rekap = $this->service->matriks($kegiatan);
        $nama  = 'absensi-' . Str::slug($kegiatan->nama) . '-' . now()->format('Ymd') . '.pdf';

        return Pdf::loadView('admin.absensi.pdf', [
            'kegiatan'  => $kegiatan,
            'sesi'      => $rekap['sesi'],
            'baris'     => $rekap['baris'],
            'ringkasan' => $this->service->ringkasan($rekap['baris']),
            'logo'       => $this->gambarDataUri(config('hima.logo')),
            'logoKampus' => $this->gambarDataUri(config('hima.logo_kampus')),
        ])->setPaper('a4', 'portrait')->download($nama);
    }

    private function paginasi(Collection $koleksi, Request $request): LengthAwarePaginator
    {
        $halaman = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $koleksi->forPage($halaman, self::PER_HALAMAN)->values(),
            $koleksi->count(),
            self::PER_HALAMAN,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /** Gambar sebagai data URI supaya DomPDF tidak bermasalah dengan path file. */
    private function gambarDataUri(?string $relatif): ?string
    {
        $path = $relatif ? public_path($relatif) : null;

        if (!$path || !is_file($path)) {
            return null;
        }

        return 'data:' . File::mimeType($path) . ';base64,' . base64_encode(file_get_contents($path));
    }
}
