@extends('layouts.admin')

@section('title', 'Struktur Pengurus')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="font-serif text-2xl font-bold text-stone-800">Struktur Pengurus</h2>
            <p class="text-sm text-stone-500">Kelola divisi dan pengurus per periode kepengurusan.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($daftarPeriode->isNotEmpty())
                <form method="GET" action="{{ route('admin.pengurus.index') }}" class="flex items-center gap-2">
                    <label for="periode" class="text-sm text-stone-500">Periode</label>
                    <select id="periode" name="periode" onchange="this.form.submit()"
                            class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                        @foreach ($daftarPeriode as $p)
                            <option value="{{ $p->id }}" @selected($periode && $periode->id === $p->id)>{{ $p->nama }}{{ $p->status === 'aktif' ? ' (aktif)' : ' — arsip' }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            @if ($periode)
                <a href="{{ route('publik.struktur', ['periode' => $periode->id]) }}" target="_blank" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">Lihat Bagan</a>
            @endif
            <a href="{{ route('admin.periode.index') }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">Kelola Periode</a>
        </div>
    </div>

    @if (!$periode)
        <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-12 text-center">
            <p class="mb-4 text-stone-500">Belum ada periode kepengurusan.</p>
            <a href="{{ route('admin.periode.index') }}" class="rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white hover:bg-maroon-dark">Buat Periode Pertama</a>
        </div>
    @else
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm text-stone-500">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $periode->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' }}">
                    {{ $periode->status === 'aktif' ? 'Periode aktif' : 'Arsip' }}
                </span>
                <span>{{ $inti->count() + $divisi->sum(fn ($d) => $d->pengurus->count()) }} pengurus · {{ $divisi->count() }} divisi</span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.divisi.create', ['periode' => $periode->id]) }}"
                   class="rounded-lg border border-maroon px-4 py-2 text-sm font-semibold text-maroon hover:bg-maroon-soft">+ Tambah Divisi</a>
                <a href="{{ route('admin.pengurus.create', ['periode' => $periode->id]) }}"
                   class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-dark">+ Tambah Pengurus</a>
            </div>
        </div>

        <div class="space-y-5">
            {{-- Pengurus inti / tanpa divisi --}}
            <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4">
                    <div>
                        <h3 class="font-serif text-lg font-bold text-stone-800">Pengurus Inti</h3>
                        <p class="text-xs text-stone-400">Ketua, wakil, sekretaris, bendahara, dan pengurus tanpa divisi</p>
                    </div>
                    <a href="{{ route('admin.pengurus.create', ['periode' => $periode->id]) }}" class="text-xs font-semibold text-maroon hover:underline">+ Tambah</a>
                </div>
                @include('admin.pengurus._tabel', ['daftar' => $inti])
            </div>

            {{-- Divisi --}}
            @foreach ($divisi as $d)
                <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-stone-200 px-5 py-4">
                        <div class="min-w-0">
                            <h3 class="font-serif text-lg font-bold text-stone-800">{{ $d->nama }}</h3>
                            @if ($d->deskripsi)
                                <p class="max-w-2xl text-xs text-stone-400">{{ $d->deskripsi }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs font-semibold">
                            <a href="{{ route('admin.pengurus.create', ['periode' => $periode->id, 'divisi' => $d->id]) }}" class="text-maroon hover:underline">+ Anggota</a>
                            <a href="{{ route('admin.divisi.edit', $d) }}" class="text-stone-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.divisi.destroy', $d) }}"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus divisi ' . $d->nama . '? Pengurus di dalamnya dipindahkan ke Tanpa divisi.') }})">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </div>
                    @include('admin.pengurus._tabel', ['daftar' => $d->pengurus])
                </div>
            @endforeach

            @if ($divisi->isEmpty())
                <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-6 text-center text-sm text-stone-400">
                    Belum ada divisi pada periode ini. Klik "Tambah Divisi" untuk membuatnya.
                </p>
            @endif
        </div>
    @endif
@endsection
