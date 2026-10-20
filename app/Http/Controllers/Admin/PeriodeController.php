<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeKepengurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodeController extends Controller
{
    public function index()
    {
        $periode = PeriodeKepengurusan::withCount(['divisi', 'pengurus'])
            ->orderByDesc('tahun_mulai')
            ->orderByDesc('id')
            ->get();

        return view('admin.periode.index', compact('periode'));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $nama = $data['tahun_mulai'] . '/' . $data['tahun_selesai'];

        if (PeriodeKepengurusan::where('nama', $nama)->exists()) {
            return back()->withInput()->withErrors(['tahun_mulai' => "Periode {$nama} sudah ada."]);
        }

        $periode = DB::transaction(function () use ($data, $nama, $request) {
            $periode = PeriodeKepengurusan::create([
                'nama'          => $nama,
                'tahun_mulai'   => $data['tahun_mulai'],
                'tahun_selesai' => $data['tahun_selesai'],
                'status'        => 'arsip',
            ]);

            if ($request->boolean('aktifkan')) {
                $this->jadikanAktif($periode);
            }

            return $periode;
        });

        return redirect()->route('admin.periode.index')->with('success', "Periode {$periode->nama} berhasil dibuat.");
    }

    public function edit(PeriodeKepengurusan $periode)
    {
        return view('admin.periode.edit', compact('periode'));
    }

    public function update(Request $request, PeriodeKepengurusan $periode)
    {
        $data = $this->validasi($request);
        $nama = $data['tahun_mulai'] . '/' . $data['tahun_selesai'];

        if (PeriodeKepengurusan::where('nama', $nama)->where('id', '!=', $periode->id)->exists()) {
            return back()->withInput()->withErrors(['tahun_mulai' => "Periode {$nama} sudah ada."]);
        }

        $periode->update([
            'nama'          => $nama,
            'tahun_mulai'   => $data['tahun_mulai'],
            'tahun_selesai' => $data['tahun_selesai'],
        ]);

        return redirect()->route('admin.periode.index')->with('success', "Periode {$nama} berhasil diperbarui.");
    }

    /** Aktifkan satu periode; periode lain otomatis diarsipkan. */
    public function aktifkan(PeriodeKepengurusan $periode)
    {
        DB::transaction(fn () => $this->jadikanAktif($periode));

        return back()->with('success', "Periode {$periode->nama} sekarang aktif. Periode lain diarsipkan.");
    }

    public function destroy(PeriodeKepengurusan $periode)
    {
        if ($periode->status === 'aktif') {
            return back()->with('error', 'Periode aktif tidak bisa dihapus. Aktifkan periode lain terlebih dahulu.');
        }

        if ($periode->divisi()->exists() || $periode->pengurus()->exists()) {
            return back()->with('error', 'Periode tidak bisa dihapus karena masih memiliki divisi atau pengurus. Data ini disimpan sebagai arsip.');
        }

        $periode->delete();

        return back()->with('success', "Periode {$periode->nama} berhasil dihapus.");
    }

    private function jadikanAktif(PeriodeKepengurusan $periode): void
    {
        PeriodeKepengurusan::where('id', '!=', $periode->id)->update(['status' => 'arsip']);
        $periode->update(['status' => 'aktif']);
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'tahun_mulai'   => ['required', 'integer', 'between:2000,2100'],
            'tahun_selesai' => ['required', 'integer', 'between:2000,2100', 'gte:tahun_mulai'],
        ], [
            'tahun_mulai.required'   => 'Tahun mulai wajib diisi.',
            'tahun_mulai.between'    => 'Tahun mulai tidak valid.',
            'tahun_selesai.required' => 'Tahun selesai wajib diisi.',
            'tahun_selesai.between'  => 'Tahun selesai tidak valid.',
            'tahun_selesai.gte'      => 'Tahun selesai tidak boleh lebih kecil dari tahun mulai.',
        ]);
    }
}
