<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\PeriodeKepengurusan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisiController extends Controller
{
    public function create(Request $request)
    {
        $periode = $this->periodeDariQuery($request);

        return view('admin.divisi.form', [
            'periode'       => $periode,
            'divisi'        => null,
            'urutanDefault' => (int) Divisi::where('periode_id', $periode->id)->max('urutan') + 1,
        ]);
    }

    public function store(Request $request)
    {
        $periode = PeriodeKepengurusan::findOrFail((int) $request->input('periode_id'));

        $data = $request->validate([
            'nama'      => ['required', 'string', 'max:100', Rule::unique('divisi', 'nama')->where('periode_id', $periode->id)],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'urutan'    => ['nullable', 'integer', 'between:0,127'],
        ], $this->pesan());

        Divisi::create([
            'periode_id' => $periode->id,
            'nama'       => $data['nama'],
            'deskripsi'  => $data['deskripsi'] ?? null,
            'urutan'     => $data['urutan'] ?? ((int) Divisi::where('periode_id', $periode->id)->max('urutan') + 1),
        ]);

        return redirect()->route('admin.pengurus.index', ['periode' => $periode->id])
            ->with('success', "Divisi {$data['nama']} berhasil ditambahkan.");
    }

    public function edit(Divisi $divisi)
    {
        return view('admin.divisi.form', [
            'periode'       => $divisi->periode,
            'divisi'        => $divisi,
            'urutanDefault' => $divisi->urutan,
        ]);
    }

    public function update(Request $request, Divisi $divisi)
    {
        $data = $request->validate([
            'nama'      => ['required', 'string', 'max:100', Rule::unique('divisi', 'nama')->where('periode_id', $divisi->periode_id)->ignore($divisi->id)],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'urutan'    => ['required', 'integer', 'between:0,127'],
        ], $this->pesan());

        // Nama divisi dikunci selama masih ada pengurus di dalamnya
        if ($data['nama'] !== $divisi->nama && $divisi->hasPengurus()) {
            return back()->withInput()->withErrors([
                'nama' => 'Nama divisi tidak dapat diubah selama masih ada pengurus di dalamnya.',
            ]);
        }

        $divisi->update([
            'nama'      => $data['nama'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'urutan'    => $data['urutan'],
        ]);

        return redirect()->route('admin.pengurus.index', ['periode' => $divisi->periode_id])
            ->with('success', "Divisi {$divisi->nama} berhasil diperbarui.");
    }

    /** Pengurus di dalamnya otomatis menjadi "tanpa divisi" (FK nullOnDelete). */
    public function destroy(Divisi $divisi)
    {
        $jumlah = $divisi->pengurus()->count();
        $divisi->delete();

        $catatan = $jumlah > 0 ? " {$jumlah} pengurus dipindahkan ke 'Tanpa divisi'." : '';

        return redirect()->route('admin.pengurus.index', ['periode' => $divisi->periode_id])
            ->with('success', "Divisi {$divisi->nama} dihapus.{$catatan}");
    }

    private function periodeDariQuery(Request $request): PeriodeKepengurusan
    {
        return PeriodeKepengurusan::find((int) $request->query('periode'))
            ?? PeriodeKepengurusan::where('status', 'aktif')->firstOrFail();
    }

    private function pesan(): array
    {
        return [
            'nama.required' => 'Nama divisi wajib diisi.',
            'nama.unique'   => 'Divisi dengan nama ini sudah ada pada periode tersebut.',
            'urutan.integer' => 'Urutan harus berupa angka.',
            'urutan.between' => 'Urutan harus antara 0 sampai 127.',
        ];
    }
}
