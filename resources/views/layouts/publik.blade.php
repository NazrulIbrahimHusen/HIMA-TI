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
    <footer class="tanpa-cetak bg-maroon-dark text-white/70">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-serif text-base font-bold text-white">{{ \Illuminate\Support\Str::title(mb_strtolower($kop['baris'][0] ?? '')) }}</p>
                <p>{{ $kop['alamat'] }}</p>
                <p>Email : {{ $kop['email'] }}</p>
            </div>
            <p class="text-xs text-white/50">&copy; {{ date('Y') }} SI HIMA Informatika</p>
        </div>
    </footer>

    @if (session('success') || session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-cloak x-transition
             class="fixed right-4 top-20 z-50 max-w-sm rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg {{ session('success') ? 'bg-emerald-700' : 'bg-red-700' }}">
            <div class="flex items-start gap-3">
                <span class="flex-1">{{ session('success') ?? session('error') }}</span>
                <button @click="show = false" class="text-white/70 hover:text-white" aria-label="Tutup">&times;</button>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>
