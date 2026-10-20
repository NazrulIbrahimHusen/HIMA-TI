@extends('layouts.admin')

@section('title', $divisi ? 'Edit Divisi' : 'Tambah Divisi')

@section('content')
    @php $terkunci = $divisi && $divisi->hasPengurus(); @endphp

    <div class="mx-auto max-w-xl">
        <a href="{{ route('admin.pengurus.index', ['periode' => $periode->id]) }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke struktur pengurus</a>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 font-serif text-xl font-bold text-stone-800">{{ $divisi ? 'Edit Divisi' : 'Tambah Divisi' }}</h2>
            <p class="mb-5 text-sm text-stone-500">Periode {{ $periode->nama }}</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ $divisi ? route('admin.divisi.update', $divisi) : route('admin.divisi.store') }}" class="space-y-4">
                @csrf
                @if ($divisi) @method('PUT') @else <input type="hidden" name="periode_id" value="{{ $periode->id }}"> @endif

                <div>
                    <label for="nama" class="mb-1 block text-sm font-medium text-stone-700">Nama divisi</label>
                    <input id="nama" type="text" name="nama" required maxlength="100" @readonly($terkunci)
                           value="{{ old('nama', $divisi->nama ?? '') }}"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon {{ $terkunci ? 'bg-stone-100 text-stone-500' : '' }}"
                           placeholder="mis. Divisi Humas">
                    @if ($terkunci)
                        <p class="mt-1 text-xs text-stone-400">Nama dikunci karena divisi ini masih memiliki pengurus.</p>
                    @endif
                </div>

                <div>
                    <label for="deskripsi" class="mb-1 block text-sm font-medium text-stone-700">Deskripsi <span class="font-normal text-stone-400">(opsional)</span></label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="1000"
                              class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">{{ old('deskripsi', $divisi->deskripsi ?? '') }}</textarea>
                </div>

                <div>
                    <label for="urutan" class="mb-1 block text-sm font-medium text-stone-700">Urutan tampil</label>
                    <input id="urutan" type="number" name="urutan" min="0" max="127" required value="{{ old('urutan', $urutanDefault) }}"
                           class="w-32 rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                    <p class="mt-1 text-xs text-stone-400">Angka kecil tampil lebih dulu di struktur dan bagan.</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.pengurus.index', ['periode' => $periode->id]) }}" class="rounded-lg border border-stone-300 px-5 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">Batal</a>
                    <button type="submit" class="rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white hover:bg-maroon-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
