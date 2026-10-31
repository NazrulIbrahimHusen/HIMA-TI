@extends('layouts.publik')

@section('title', 'Beranda')

@push('head')
    <style>
        /* Header selalu menempel di paling atas */
        body > header.sticky { position: -webkit-sticky; position: sticky; top: 0; z-index: 50; }
    </style>
@endpush

@section('content')

    {{-- Hero (video) --}}
    <section class="relative isolate overflow-hidden bg-maroon-dark text-white">
        <video class="absolute inset-0 -z-20 h-full w-full object-cover object-[80%_20%] md:object-[center_15%] motion-reduce:hidden"
               autoplay muted loop playsinline preload="metadata"
               poster="{{ asset('media/hero/hero-poster.jpg') }}">
            <source src="{{ asset('media/hero/hero.mp4') }}" type="video/mp4">
        </video>

        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-maroon-dark/70 via-maroon-dark/45 to-transparent"></div>

        <div class="mx-auto flex h-[26rem] max-w-6xl items-center px-4 sm:h-[30rem] md:h-[56.25vw] md:max-h-[44rem] md:min-h-[32rem]">
            <div class="max-w-[64%] sm:max-w-md md:max-w-2xl">
                <span class="mb-3 block h-0.5 w-10 bg-rose-400"></span>
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-widest text-white/80 md:mb-3 md:text-xs">HIMA Informatika</p>
                <h1 class="font-serif text-2xl font-bold leading-tight sm:text-3xl lg:text-5xl">
                    Bersama Membangun <span class="text-rose-200">Kolaborasi, Inovasi,</span> dan Dampak
                </h1>
                <p class="mt-3 text-xs leading-relaxed text-white/85 sm:text-sm md:mt-4 md:text-base">
                    HIMA Informatika {{ \Illuminate\Support\Str::title(mb_strtolower(config('hima.kop.baris.2', ''))) }} menjadi wadah pengembangan diri,
                    kreativitas, dan kontribusi mahasiswa Informatika untuk masa depan yang lebih baik.
                </p>
                <div class="mt-5 flex flex-wrap gap-3 md:mt-7">
                    <a href="{{ route('publik.kegiatan.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-maroon transition hover:bg-white/90 md:px-6 md:py-3">
                        Jelajahi Kegiatan <span aria-hidden="true">&rarr;</span>
                    </a>
                    @guest
                        <a href="{{ route('mahasiswa.login') }}" class="hidden rounded-full border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10 sm:inline-block">Masuk Mahasiswa</a>
                    @endguest
                </div>
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

        {{-- CTA Banner (pola sama dengan hero) --}}
    <section class="bg-maroon-dark text-white">
        <div class="relative isolate mx-auto h-[26rem] max-w-[1920px] overflow-hidden sm:h-auto sm:aspect-[12/5]">

            {{-- Gambar latar penuh. Mobile: fokus ke kanan supaya orang tetap terlihat --}}
            <img src="{{ asset('media/hero/cta-banner.png') }}" alt=""
                 class="absolute inset-0 -z-20 h-full w-full object-cover object-[75%_center] sm:object-center"
                 loading="lazy" onerror="this.remove()">

            {{-- Overlay: mobile gelap dari bawah (area teks), tablet+ dari kiri --}}
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-maroon-dark via-maroon-dark/60 to-transparent sm:bg-gradient-to-r sm:from-maroon-dark/90 sm:via-maroon-dark/25 sm:to-transparent"></div>

            {{-- Konten: mobile di bawah, tablet+ di tengah vertikal --}}
            <div class="absolute inset-0 flex items-end pb-6 sm:items-center sm:pb-0">
                <div class="mx-auto w-full max-w-6xl px-4">
                    <div class="max-w-[62%] min-[400px]:max-w-[56%] sm:max-w-[52%] lg:max-w-xl xl:max-w-2xl">
                        <h2 class="font-serif text-xl font-bold leading-tight sm:text-xl md:text-3xl lg:text-4xl">
                            Jadilah Bagian dari Perubahan!
                        </h2>
                        <p class="mt-2 text-xs leading-relaxed text-white/85 sm:text-xs md:mt-3 md:text-base">
                            Bersama HIMA Informatika, kembangkan potensi, perluas relasi, dan ciptakan inovasi yang berdampak bagi sesama.
                        </p>
                        <div class="mt-4 md:mt-6">
                            <a href="{{ route('publik.kegiatan.index') }}"
                               class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold text-maroon transition hover:bg-white/90 md:px-6 md:py-3 md:text-sm">
                                Jelajahi Kegiatan Kami <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection