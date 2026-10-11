<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AnggotaTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\AnggotaImport;
use App\Models\Anggota;
use App\Services\AnggotaService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AnggotaController extends Controller
{
    public function __construct(private AnggotaService $service)
    {
    }

    public function index(Request $request)
    {
        $anggota = Anggota::query()
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->filled('prodi'), fn ($q) => $q->where('prodi', $request->prodi))
            ->when($request->filled('angkatan'), fn ($q) => $q->where('angkatan', $request->angkatan))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();

        // Search real-time (AJAX) hanya butuh tabelnya saja
        if ($request->ajax()) {
            return view('admin.anggota._table', compact('anggota'));
        }

        return view('admin.anggota.index', [
            'anggota'      => $anggota,
            'prodiList'    => $this->prodiList(),
            'angkatanList' => Anggota::query()->select('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
        ]);
    }

    public function create()
    {
        return view('admin.anggota.create', ['prodiList' => $this->prodiList()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nim'      => ['required', 'digits_between:1,10', Rule::unique('anggota', 'nim')->whereNull('deleted_at')],
            'nama'     => ['required', 'string', 'max:255'],
            'prodi'    => ['required', 'string', 'max:100'],
            'angkatan' => ['required', 'integer', 'between:2000,' . (date('Y') + 1)],
            'semester' => ['required', 'integer', 'between:1,14'],
            'status'   => ['required', 'in:aktif,tidak_aktif'],
            'foto'     => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], $this->pesan());

        [$anggota, $hasil] = $this->service->simpan($data, $request->file('foto'));

        $pesan = $hasil === AnggotaService::HASIL_DIPULIHKAN
            ? "Anggota {$anggota->nama} dipulihkan dari data yang pernah dihapus. Password direset ke NIM."
            : "Anggota {$anggota->nama} berhasil ditambahkan. Password awal = NIM.";

        return redirect()->route('admin.anggota.index')->with('success', $pesan);
    }

    public function edit(Anggota $anggota)
    {
        return view('admin.anggota.edit', [
            'anggota'   => $anggota,
            'prodiList' => $this->prodiList(),
        ]);
    }

    public function update(Request $request, Anggota $anggota)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'prodi'    => ['required', 'string', 'max:100'],
            'angkatan' => ['required', 'integer', 'between:2000,' . (date('Y') + 1)],
            'semester' => ['required', 'integer', 'between:1,14'],
            'status'   => ['required', 'in:aktif,tidak_aktif'],
            'foto'     => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], $this->pesan());

        $this->service->perbarui($anggota, $data, $request->file('foto'));

        return redirect()->route('admin.anggota.index')->with('success', "Data {$anggota->nama} berhasil diperbarui.");
    }

    public function destroy(Anggota $anggota)
    {
        try {
            $this->service->hapus($anggota);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.anggota.index')->with('success', "Anggota {$anggota->nama} berhasil dihapus.");
    }

    public function resetPassword(Anggota $anggota)
    {
        $this->service->resetPassword($anggota);

        return back()->with('success', "Password {$anggota->nama} direset ke NIM. Wajib diganti saat login berikutnya.");
    }

    // ── Import ───────────────────────────────────────────────────────────────

    public function importForm()
    {
        return view('admin.anggota.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [
            'file.required' => 'Pilih file terlebih dahulu.',
            'file.mimes'    => 'File harus berformat .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        set_time_limit(0); // hashing password per baris butuh waktu untuk file besar

        $import = new AnggotaImport($this->service);
        Excel::import($import, $request->file('file'));

        if ($import->formatSalah) {
            return back()->with('error', 'Format kolom tidak sesuai. Gunakan template dengan kolom: nim, nama, prodi, angkatan, semester.');
        }

        return redirect()->route('admin.anggota.import')
            ->with('import_result', [
                'dibuat'     => $import->dibuat,
                'dipulihkan' => $import->dipulihkan,
                'dilewati'   => $import->dilewati,
                'gagal'      => array_slice($import->gagal, 0, 50),
                'total_gagal' => count($import->gagal),
            ])
            ->with('success', "Import selesai: {$import->dibuat} anggota baru ditambahkan.");
    }

    public function template()
    {
        return Excel::download(new AnggotaTemplateExport(), 'template-import-anggota.xlsx');
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    private function prodiList()
    {
        return Anggota::query()->select('prodi')->distinct()->orderBy('prodi')->pluck('prodi');
    }

    private function pesan(): array
    {
        return [
            'nim.required'        => 'NIM wajib diisi.',
            'nim.digits_between'  => 'NIM harus berupa angka, maksimal 10 digit.',
            'nim.unique'          => 'NIM sudah terdaftar.',
            'nama.required'       => 'Nama wajib diisi.',
            'prodi.required'      => 'Program studi wajib diisi.',
            'angkatan.required'   => 'Angkatan wajib diisi.',
            'angkatan.between'    => 'Angkatan tidak valid.',
            'semester.required'   => 'Semester wajib diisi.',
            'semester.between'    => 'Semester harus antara 1 sampai 14.',
            'foto.image'          => 'File harus berupa gambar.',
            'foto.mimes'          => 'Foto harus berformat JPG atau PNG.',
            'foto.max'            => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
