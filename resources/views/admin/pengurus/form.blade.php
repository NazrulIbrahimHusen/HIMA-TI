@extends('layouts.admin')

@section('title', $pengurus ? 'Edit Pengurus' : 'Tambah Pengurus')

@section('content')
    @php
        // NIM yang tampil di input (old input > NIM dari tombol "Jadikan pengurus")
        $nimIsi  = old('nim', $nimAwal ?? '');
        $namaIsi = $pengurus ? null : ($calonList->firstWhere('nim', $nimIsi)?->nama ?? null);
    @endphp

    <div class="mx-auto max-w-xl">
        <a href="{{ route('admin.pengurus.index', ['periode' => $periode->id]) }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke struktur pengurus</a>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 font-serif text-xl font-bold text-stone-800">{{ $pengurus ? 'Edit Pengurus' : 'Tambah Pengurus' }}</h2>
            <p class="mb-5 text-sm text-stone-500">Periode {{ $periode->nama }}</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ $pengurus ? route('admin.pengurus.update', $pengurus) : route('admin.pengurus.store') }}" class="space-y-4">
                @csrf
                @if ($pengurus) @method('PUT') @else <input type="hidden" name="periode_id" value="{{ $periode->id }}"> @endif

                <div>
                    <label for="nim" class="mb-1 block text-sm font-medium text-stone-700">Anggota (NIM)</label>
                    @if ($pengurus)
                        <input id="nim" type="text" value="{{ $pengurus->anggota?->nim }} — {{ $pengurus->anggota?->nama }}" readonly
                               class="w-full rounded-lg border border-stone-300 bg-stone-100 px-3 py-2.5 text-sm text-stone-500">
                    @else
                        <input id="nim" type="text" name="nim" list="daftar-calon" required maxlength="10" inputmode="numeric" autocomplete="off"
                               value="{{ $nimIsi }}" placeholder="Ketik NIM atau nama untuk memilih"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2.5 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                        <datalist id="daftar-calon">
                            @foreach ($calonList as $c)
                                <option value="{{ $c->nim }}" label="{{ $c->nama }}">{{ $c->nama }}</option>
                            @endforeach
                        </datalist>
                        @if ($namaIsi)
                            <p class="mt-1 text-sm font-semibold text-maroon">{{ $namaIsi }}</p>
                        @endif
                        <p class="mt-1 text-xs text-stone-400">Hanya anggota aktif yang belum menjadi pengurus pada periode ini.</p>
                    @endif
                </div>

                <div>
                    <label for="jabatan" class="mb-1 block text-sm font-medium text-stone-700">Jabatan</label>
                    <input id="jabatan" type="text" name="jabatan" list="daftar-jabatan" required maxlength="100"
                           value="{{ old('jabatan', $pengurus->jabatan ?? '') }}"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon"
                           placeholder="mis. Ketua, Sekretaris, Koordinator Humas">
                    <datalist id="daftar-jabatan">
                        @foreach (['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Koordinator Divisi', 'Anggota Divisi'] as $j)
                            <option value="{{ $j }}">
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label for="divisi_id" class="mb-1 block text-sm font-medium text-stone-700">Divisi</label>
                    <select id="divisi_id" name="divisi_id"
                            class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                        <option value="">— Tanpa divisi (pengurus inti) —</option>
                        @foreach ($divisiList as $d)
                            <option value="{{ $d->id }}" @selected((string) old('divisi_id', $pengurus->divisi_id ?? $divisiAwal) === (string) $d->id)>{{ $d->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="urutan" class="mb-1 block text-sm font-medium text-stone-700">Urutan tampil {{ $pengurus ? '' : '(opsional)' }}</label>
                    <input id="urutan" type="number" name="urutan" min="0" max="127" {{ $pengurus ? 'required' : '' }}
                           value="{{ old('urutan', $pengurus->urutan ?? '') }}"
                           class="w-32 rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                    <p class="mt-1 text-xs text-stone-400">Angka kecil tampil lebih dulu. Kosongkan untuk ditaruh di urutan terakhir.</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.pengurus.index', ['periode' => $periode->id]) }}" class="rounded-lg border border-stone-300 px-5 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">Batal</a>
                    <button type="submit" class="rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white hover:bg-maroon-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection