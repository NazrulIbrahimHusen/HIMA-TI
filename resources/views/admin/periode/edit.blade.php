@extends('layouts.admin')

@section('title', 'Edit Periode')

@section('content')
    <div class="mx-auto max-w-xl">
        <a href="{{ route('admin.periode.index') }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke daftar periode</a>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 font-serif text-xl font-bold text-stone-800">Edit Periode {{ $periode->nama }}</h2>
            <p class="mb-5 text-sm text-stone-500">Nama periode dibuat otomatis dari tahun mulai dan tahun selesai.</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.periode.update', $periode) }}" class="grid grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="tahun_mulai" class="mb-1 block text-xs font-medium text-stone-500">Tahun mulai</label>
                    <input id="tahun_mulai" type="number" name="tahun_mulai" min="2000" max="2100" required
                           value="{{ old('tahun_mulai', $periode->tahun_mulai) }}"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>
                <div>
                    <label for="tahun_selesai" class="mb-1 block text-xs font-medium text-stone-500">Tahun selesai</label>
                    <input id="tahun_selesai" type="number" name="tahun_selesai" min="2000" max="2100" required
                           value="{{ old('tahun_selesai', $periode->tahun_selesai) }}"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>
                <div class="col-span-2 mt-2 flex justify-end gap-3">
                    <a href="{{ route('admin.periode.index') }}" class="rounded-lg border border-stone-300 px-5 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">Batal</a>
                    <button type="submit" class="rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white hover:bg-maroon-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
