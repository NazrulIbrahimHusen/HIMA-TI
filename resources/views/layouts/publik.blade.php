@php
    $menu = [
        ['Beranda',    'home',                    'home'],
        ['Kegiatan',   'publik.kegiatan.index',   'publik.kegiatan.*'],
        ['Pengumuman', 'publik.pengumuman.index', 'publik.pengumuman.*'],
        ['Struktur',   'publik.struktur',         'publik.struktur'],
    ];
    $kop   = config('hima.kop');
    $kampus = \Illuminate\Support\Str::title(mb_strtolower($kop['baris'][2] ?? ''));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') — HIMA Informatika {{ $kampus }}</title>
    @include('layouts._tema')
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-[#FAF6F5] font-sans text-stone-800">

    {{-- Navigasi --}}
    <header class="tanpa-cetak sticky top-0 z-30 bg-maroon-dark text-white" x-data="{ menu: false }">
        <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-logo tinggi="h-9" />
                <span class="hidden leading-tight sm:block">
                    <span class="block font-serif text-base font-bold">HIMA Informatika</span>
                    <span class="block text-xs text-white/60">{{ $kampus }}</span>
                </span>
            </a>

            <nav class="ml-auto hidden items-center gap-1 md:flex">
                @foreach ($menu as [$nama, $rute, $pola])
                    <a href="{{ route($rute) }}"
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs($pola) ? 'bg-white/15 text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">{{ $nama }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2 md:ml-3">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('mahasiswa.dashboard') }}"
                       class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-maroon transition hover:bg-white/90">
                        {{ auth()->user()->isAdmin() ? 'Panel Admin' : 'Portal Saya' }}
                    </a>
                @else
                    <a href="{{ route('mahasiswa.login') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-maroon transition hover:bg-white/90">Masuk</a>
                @endauth

                <button type="button" @click="menu = !menu" class="rounded-lg p-2 text-white/80 hover:bg-white/10 md:hidden" aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                </button>
            </div>
        </div>

        <nav x-cloak x-show="menu" class="border-t border-white/10 px-4 pb-3 md:hidden">
            @foreach ($menu as [$nama, $rute, $pola])
                <a href="{{ route($rute) }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs($pola) ? 'bg-white/15' : 'text-white/75' }}">{{ $nama }}</a>
            @endforeach
        </nav>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>
    {{-- Footer --}}
    @php
        $sosmed = [
            'HIMA Informatika' => [
                ['Hima Informatika Unilam', 'instagram', 'https://www.instagram.com/hima.informatika.unilam?mdxt=MTlzbnV3N2FsaWFhYQ=='],
                ['Hima Informatika Unilam',    'tiktok',    'https://www.tiktok.com/@hima.informatika.unilam?_r=1&_t=ZS-9AxMNEkFoOK'],
            ],
            'Universitas & Prgram Studi' => [
                ['Universitas La Tansa Mashiro',    'instagram', 'https://www.instagram.com/unilam.official?mdxt=eHhseWMzZjVibTFu'],
                ['Program Studi Informatika', 'tiktok',    'https://www.tiktok.com/@informatika.unilam?_r=1&_t=ZS-9AxMPfLIQNx'],
            ],
        ];
    @endphp
    <footer class="tanpa-cetak bg-maroon-dark text-white/70">
        <div class="mx-auto max-w-6xl px-4 py-10">
            <div class="grid gap-10 md:grid-cols-2">

                {{-- Identitas & kontak --}}
                <div>
                    <p class="font-serif text-2xl font-bold text-white">HIMA Informatika</p>
                    <p class="mt-1 text-base text-white/80">{{ $kampus }}</p>

                    <ul class="mt-6 space-y-4 text-sm">
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-white/80" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $kop['alamat'] }}</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-white/80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                            </svg>
                            <a href="mailto:{{ $kop['email'] }}" class="hover:text-white transition">{{ $kop['email'] }}</a>
                        </li>
                    </ul>
                </div>

                {{-- Ikuti Kami --}}
                <div>
                    <p class="font-serif text-2xl font-bold text-white">Ikuti Kami</p>

                    @foreach ($sosmed as $judul => $daftar)
                        <p class="mb-3 mt-6 text-sm font-semibold text-white/60">{{ $judul }}</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($daftar as [$label, $ikon, $url])
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                   class="group flex items-center gap-3 rounded-xl border border-white/20 px-3 py-2.5 text-sm text-white transition hover:border-white/50 hover:bg-white/5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10">
                                        @if ($ikon === 'instagram')
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/>
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="flex-1 truncate">{{ $label }}</span>
                                    <svg class="h-4 w-4 shrink-0 text-white/60 transition group-hover:translate-x-0.5 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Garis & hak cipta --}}
            <div class="mt-10 border-t border-white/10 pt-5 text-right text-xs text-white/50">
                &copy; {{ date('Y') }} SI HIMA Informatika
            </div>
        </div>
    </footer>
    
</body>
</html>
