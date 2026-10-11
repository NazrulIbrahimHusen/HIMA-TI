<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kegiatan;
use App\Services\KegiatanService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KegiatanController extends Controller
{
    public function __construct(private KegiatanService $service)
    {
    }

    public function index(Request $request)
    {
        $kegiatan = Kegiatan::query()
            ->withCount('sesiAbsensi')
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('tipe'), fn ($q) => $q->where('tipe', $request->tipe))
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.kegiatan._table', compact('kegiatan'));
        }

        return view('admin.kegiatan.index', compact('kegiatan'));
    }

    public function create()
    {
        return view('admin.kegiatan.create');
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $kegiatan = $this->service->simpan(Arr::except($data, ['sesi']), array_values($data['sesi']));

        return redirect()->route('admin.kegiatan.show', $kegiatan)
            ->with('success', 'Kegiatan dibuat sebagai draft. Publikasikan agar link & QR absensi aktif.');
    }

    public function show(Kegiatan $kegiatan)
    {
        $kegiatan->load(['sesiAbsensi' => fn ($q) => $q->orderBy('waktu_mulai')->withCount([
            'absensi',
            'absensi as hadir_count' => fn ($a) => $a->where('status', 'hadir'),
        ])]);

        $milikKegiatan = fn ($q) => $q->where('kegiatan_id', $kegiatan->id);

        $absensiTerbaru = Absensi::with('sesi')->whereHas('sesi', $milikKegiatan)->latest()->limit(20)->get();
        $totalHadir     = Absensi::whereHas('sesi', $milikKegiatan)->where('status', 'hadir')->count();

        $linkAbsen = route('absen.form', $kegiatan->token);
        $qrSvg = $kegiatan->status === 'published'
            ? (string) QrCode::format('svg')->size(260)->margin(1)->errorCorrection('M')->generate($linkAbsen . '?via=qr')
            : null;

        return view('admin.kegiatan.show', compact('kegiatan', 'absensiTerbaru', 'totalHadir', 'linkAbsen', 'qrSvg'));
    }

    public function edit(Kegiatan $kegiatan)
    {
        $kegiatan->load('sesiAbsensi');

        return view('admin.kegiatan.edit', compact('kegiatan'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $this->validasi($request);

        try {
            $this->service->perbarui($kegiatan, Arr::except($data, ['sesi']), array_values($data['sesi']));
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.kegiatan.show', $kegiatan)->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        if (!$kegiatan->isDeletable()) {
            return back()->with('error', 'Kegiatan tidak bisa dihapus karena sudah memiliki data absensi.');
        }

        $kegiatan->delete();

        return redirect()->route('admin.kegiatan.index')->with('success', "Kegiatan \"{$kegiatan->nama}\" berhasil dihapus.");
    }

    public function ubahStatus(Request $request, Kegiatan $kegiatan)
    {
        $request->validate(['status' => ['required', 'in:published,closed']]);

        $kegiatan->update(['status' => $request->status]);

        $pesan = $request->status === 'published'
            ? 'Kegiatan dipublikasikan. Link & QR absensi sekarang aktif pada jadwal sesi.'
            : 'Kegiatan ditutup. Link & QR absensi tidak lagi menerima absensi.';

        return back()->with('success', $pesan);
    }

    public function buatTokenBaru(Kegiatan $kegiatan)
    {
        $this->service->buatTokenBaru($kegiatan);

        return back()->with('success', 'Token baru dibuat. Link dan QR lama otomatis tidak berlaku.');
    }

    /** Layar penuh untuk diproyeksikan saat kegiatan. */
    public function qr(Kegiatan $kegiatan)
    {
        abort_unless($kegiatan->status === 'published', 404);

        $kegiatan->load(['sesiAbsensi' => fn ($q) => $q->orderBy('waktu_mulai')]);

        $linkAbsen = route('absen.form', $kegiatan->token);
        $qrSvg = (string) QrCode::format('svg')->size(420)->margin(1)->errorCorrection('M')->generate($linkAbsen . '?via=qr');

        return view('admin.kegiatan.qr', compact('kegiatan', 'linkAbsen', 'qrSvg'));
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama'                 => ['required', 'string', 'max:255'],
            'tipe'                 => ['required', 'in:internal,kolaborasi'],
            'lokasi'               => ['nullable', 'string', 'max:255'],
            'deskripsi'            => ['nullable', 'string', 'max:5000'],
            'sesi'                 => ['required', 'array', 'min:1', 'max:10'],
            'sesi.*.id'            => ['nullable', 'integer'],
            'sesi.*.nama_sesi'     => ['required', 'string', 'max:100'],
            'sesi.*.waktu_mulai'   => ['required', 'date'],
            'sesi.*.waktu_selesai' => ['required', 'date', 'after:sesi.*.waktu_mulai'],
        ], [
            'nama.required'                   => 'Nama kegiatan wajib diisi.',
            'tipe.required'                   => 'Tipe kegiatan wajib dipilih.',
            'sesi.required'                   => 'Minimal harus ada satu sesi absensi.',
            'sesi.min'                        => 'Minimal harus ada satu sesi absensi.',
            'sesi.max'                        => 'Maksimal 10 sesi per kegiatan.',
            'sesi.*.nama_sesi.required'       => 'Nama sesi wajib diisi.',
            'sesi.*.waktu_mulai.required'     => 'Waktu mulai sesi wajib diisi.',
            'sesi.*.waktu_mulai.date'         => 'Waktu mulai sesi tidak valid.',
            'sesi.*.waktu_selesai.required'   => 'Waktu selesai sesi wajib diisi.',
            'sesi.*.waktu_selesai.date'       => 'Waktu selesai sesi tidak valid.',
            'sesi.*.waktu_selesai.after'      => 'Waktu selesai sesi harus setelah waktu mulainya.',
        ]);
    }
}
