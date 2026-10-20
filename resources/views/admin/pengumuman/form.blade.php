@extends('layouts.admin')

@section('title', $pengumuman ? 'Edit Pengumuman' : 'Buat Pengumuman')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('admin.pengumuman.index') }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke daftar</a>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 font-serif text-xl font-bold text-stone-800">{{ $pengumuman ? 'Edit Pengumuman' : 'Buat Pengumuman' }}</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    <ul class="list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $pengumuman ? route('admin.pengumuman.update', $pengumuman) : route('admin.pengumuman.store') }}" class="space-y-4">
                @csrf
                @if ($pengumuman) @method('PUT') @endif

                <div>
                    <label for="judul" class="mb-1 block text-sm font-medium text-stone-700">Judul</label>
                    <input id="judul" type="text" name="judul" required maxlength="255" value="{{ old('judul', $pengumuman->judul ?? '') }}"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>

                <div>
                    <label for="konten" class="mb-1 block text-sm font-medium text-stone-700">Isi pengumuman</label>
                    <textarea id="konten" name="konten" rows="12" required maxlength="20000"
                              class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">{{ old('konten', $pengumuman->konten ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-stone-400">Teks biasa. Baris baru akan dipertahankan saat ditampilkan.</p>
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-stone-700">Status</label>
                    @php $statusSaat = old('status', ($pengumuman && $pengumuman->isPublished()) ? 'terbit' : ($pengumuman ? 'draft' : 'terbit')); @endphp
                    <select id="status" name="status" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon sm:w-64">
                        <option value="terbit" @selected($statusSaat === 'terbit')>Terbit (tampil ke publik)</option>
                        <option value="draft" @selected($statusSaat === 'draft')>Draft (belum tampil)</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.pengumuman.index') }}" class="rounded-lg border border-stone-300 px-5 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">Batal</a>
                    <button type="submit" class="rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white hover:bg-maroon-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
