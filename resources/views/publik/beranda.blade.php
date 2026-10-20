@extends('layouts.publik')

@section('title', 'Beranda')

@section('content')
    {{-- Hero --}}
    <section class="bg-gradient-to-br from-maroon-dark to-maroon text-white">
        <div class="mx-auto flex max-w-6xl flex-col items-center gap-8 px-4 py-14 text-center md:flex-row md:py-20 md:text-left">
            <div class="flex-1">
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-white/70">HIMA Informatika</p>
                <h1 class="font-serif text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                    Bersama Membangun <span class="text-rose-200">Kolaborasi, Inovasi,</span> dan Dampak
                </h1>
                <p class="mt-4 max-w-xl text-white/80">
                    HIMA Informatika {{ \Illuminate\Support\Str::title(mb_strtolower(config('hima.kop.baris.2', ''))) }} menjadi wadah pengembangan diri,
                    kreativitas, dan kontribusi mahasiswa Informatika untuk masa depan yang lebih baik.
                </p>
                <div class="mt-7 flex flex-wrap justify-center gap-3 md:justify-start">
                    <a href="{{ route('publik.kegiatan.index') }}" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-maroon transition hover:bg-white/90">Jelajahi Kegiatan</a>
                    @guest
                        <a href="{{ route('mahasiswa.login') }}" class="rounded-full border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Masuk Mahasiswa</a>
                    @endguest
                </div>
            </div>
            <div class="shrink-0">
                <x-logo tinggi="h-44 sm:h-56" besar />
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl space-y-12 px-4 py-10">
        {{-- Statistik --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([['Anggota aktif', $jumlahAnggota], ['Kegiatan terselenggara', $jumlahKegiatan], ['Pengurus periode ini', $jumlahPengurus]] as [$label, $nilai])
                <div class="rounded-2xl border border-stone-200 bg-white p-5 text-center shadow-sm">
                    <p class="font-serif text-4xl font-bold text-maroon">{{ $nilai }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ $label }}</p>
                </div>
            @endforeach
        </section>

        {{-- Kegiatan terbaru --}}
        <section>
            <div class="mb-5 flex items-end justify-between">
                <h2 class="font-serif text-2xl font-bold text-stone-800">Kegiatan Terbaru</h2>
                <a href="{{ route('publik.kegiatan.index') }}" class="text-sm font-semibold text-maroon hover:underline">Lihat semua &rarr;</a>
            </div>

            @if ($kegiatan->isEmpty())
                <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center text-sm text-stone-400">Belum ada kegiatan yang dipublikasikan.</p>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @foreach ($kegiatan as $k)
                        @include('publik.kegiatan._kartu', ['k' => $k])
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Pengumuman + struktur --}}
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="mb-5 flex items-end justify-between">
                    <h2 class="font-serif text-2xl font-bold text-stone-800">Pengumuman</h2>
                    <a href="{{ route('publik.pengumuman.index') }}" class="text-sm font-semibold text-maroon hover:underline">Lihat semua &rarr;</a>
                </div>

                <div class="space-y-3">
                    @forelse ($pengumuman as $p)
                        <a href="{{ route('publik.pengumuman.show', $p) }}" class="block rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                            <p class="text-xs text-stone-400">{{ $p->published_at->locale('id')->translatedFormat('d F Y') }}</p>
                            <h3 class="mt-1 font-serif text-lg font-bold text-stone-800">{{ $p->judul }}</h3>
                            <p class="mt-1 text-sm text-stone-500">{{ \Illuminate\Support\Str::limit(strip_tags($p->konten), 140) }}</p>
                        </a>
                    @empty
                        <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center text-sm text-stone-400">Belum ada pengumuman.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl bg-gradient-to-br from-maroon-dark to-maroon p-6 text-white shadow-sm">
                <h2 class="font-serif text-xl font-bold">Struktur Kepengurusan</h2>
                <p class="mt-2 text-sm text-white/80">Kenali pengurus dan divisi yang menjalankan HIMA pada periode ini.</p>
                <a href="{{ route('publik.struktur') }}" class="mt-5 inline-block rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-maroon hover:bg-white/90">Lihat Bagan</a>
            </div>
        </section>
    </div>
@endsection
