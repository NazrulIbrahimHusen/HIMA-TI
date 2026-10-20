<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $pengumuman = Pengumuman::query()
            ->with('postedBy')
            ->when($request->filled('q'), fn ($q) => $q->where('judul', 'like', '%' . $request->q . '%'))
            ->when($request->status === 'terbit', fn ($q) => $q->whereNotNull('published_at'))
            ->when($request->status === 'draft', fn ($q) => $q->whereNull('published_at'))
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(25)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.pengumuman._tabel', compact('pengumuman'));
        }

        return view('admin.pengumuman.index', compact('pengumuman'));
    }

    public function create()
    {
        return view('admin.pengumuman.form', ['pengumuman' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $pengumuman = Pengumuman::create([
            'judul'        => $data['judul'],
            'konten'       => $data['konten'],
            'posted_by'    => $request->user()->id,
            'published_at' => $data['status'] === 'terbit' ? now() : null,
        ]);

        return redirect()->route('admin.pengumuman.index')->with('success', $data['status'] === 'terbit'
            ? "Pengumuman \"{$pengumuman->judul}\" diterbitkan."
            : "Pengumuman \"{$pengumuman->judul}\" disimpan sebagai draft.");
    }

    public function edit(Pengumuman $pengumuman)
    {
        return view('admin.pengumuman.form', compact('pengumuman'));
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $data = $this->validasi($request);

        $pengumuman->update([
            'judul'        => $data['judul'],
            'konten'       => $data['konten'],
            // Tanggal terbit asli dipertahankan jika sudah pernah terbit
            'published_at' => $data['status'] === 'terbit' ? ($pengumuman->published_at ?? now()) : null,
        ]);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return redirect()->route('admin.pengumuman.index')->with('success', "Pengumuman \"{$pengumuman->judul}\" dihapus.");
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'judul'  => ['required', 'string', 'max:255'],
            'konten' => ['required', 'string', 'max:20000'],
            'status' => ['required', 'in:draft,terbit'],
        ], [
            'judul.required'  => 'Judul wajib diisi.',
            'konten.required' => 'Isi pengumuman wajib diisi.',
            'status.required' => 'Pilih status pengumuman.',
        ]);
    }
}
