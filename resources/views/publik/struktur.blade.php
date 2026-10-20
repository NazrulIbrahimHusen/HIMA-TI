@extends('layouts.publik')

@section('title', 'Struktur Pengurus')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">
        <div class="tanpa-cetak mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-serif text-3xl font-bold text-stone-800">Struktur Kepengurusan</h1>
                <p class="text-sm text-stone-500">Pengurus dan divisi HIMA Informatika.</p>
            </div>

            @if ($periode)
                <div class="flex flex-wrap items-center gap-2">
                    @if ($daftarPeriode->count() > 1)
                        <form method="GET" action="{{ route('publik.struktur') }}" class="flex items-center gap-2">
                            <label for="periode" class="text-sm text-stone-500">Periode</label>
                            <select id="periode" name="periode" onchange="this.form.submit()"
                                    class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                                @foreach ($daftarPeriode as $p)
                                    <option value="{{ $p->id }}" @selected($periode->id === $p->id)>{{ $p->nama }}{{ $p->status === 'aktif' ? ' (aktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <button type="button" id="unduh-bagan" class="rounded-lg border border-maroon px-4 py-2 text-sm font-semibold text-maroon hover:bg-maroon-soft disabled:opacity-50">Unduh PNG</button>
                    <button type="button" onclick="window.print()" class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-dark">Cetak / Simpan PDF</button>
                </div>
            @endif
        </div>

        @if (!$periode || ($inti->isEmpty() && $divisi->isEmpty()))
            <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-12 text-center text-sm text-stone-400">Struktur kepengurusan belum tersedia.</p>
        @else
            @php
                $puncak = $inti->first();
                $inti_lain = $inti->slice(1);
            @endphp

            <div id="bagan" class="rounded-2xl bg-[#FAF6F5] p-4 sm:p-8">
                <div class="mb-8 text-center">
                    <p class="text-xs font-semibold uppercase tracking-widest text-maroon">HIMA Informatika</p>
                    <h2 class="font-serif text-2xl font-bold text-stone-800">Periode {{ $periode->nama }}</h2>
                </div>

                {{-- Pengurus inti --}}
                @if ($puncak)
                    <div class="flex justify-center"><x-kartu-pengurus :p="$puncak" besar lebar="w-56" /></div>
                @endif

                @if ($inti_lain->isNotEmpty())
                    <div class="mx-auto h-6 w-0 border-l-2 border-stone-300"></div>
                    <div class="flex flex-wrap justify-center gap-4">
                        @foreach ($inti_lain as $p)
                            <x-kartu-pengurus :p="$p" />
                        @endforeach
                    </div>
                @endif

                {{-- Divisi --}}
                @if ($divisi->isNotEmpty())
                    <div class="mx-auto h-8 w-0 border-l-2 border-stone-300"></div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($divisi as $d)
                            <div class="rounded-2xl border border-stone-200 bg-white/60 p-4">
                                <h3 class="mb-3 text-center font-serif text-base font-bold text-maroon">{{ $d->nama }}</h3>
                                <div class="space-y-3">
                                    @forelse ($d->pengurus as $p)
                                        <x-kartu-pengurus :p="$p" lebar="w-full" />
                                    @empty
                                        <p class="text-center text-xs text-stone-400">Belum ada anggota.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
