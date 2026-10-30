@php
    $nav = [
        ['label' => 'Dashboard',   'short' => 'Dashboard',  'route' => 'mahasiswa.dashboard',  'icon' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['label' => 'Absensi',     'short' => 'Absensi',    'route' => 'mahasiswa.absensi',    'match' => 'mahasiswa.absensi', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Monitoring',  'short' => 'Monitoring', 'route' => 'mahasiswa.monitoring', 'match' => 'mahasiswa.monitoring', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
        ['label' => 'KTA Digital', 'short' => 'KTA',        'route' => 'mahasiswa.kta',        'match' => 'mahasiswa.kta', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
        ['label' => 'Profil',      'short' => 'Profil',     'route' => 'mahasiswa.profil',     'match' => 'mahasiswa.profil', 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
    ];
    $anggota = auth()->user()->anggota ?? null;
    $inisial = strtoupper(mb_substr(auth()->user()->name, 0, 1));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') — SI HIMA Informatika</title>
    @include('layouts._tema')

    {{-- CSS struktur layout: ditulis manual agar tidak bergantung pada build Tailwind --}}
    <style>
        [x-cloak] { display: none !important; }

        body.m-body { padding-bottom: 72px; }

        .m-sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 40;
            width: 16rem; display: flex; flex-direction: column;
            transform: translateX(-100%); transition: transform .2s ease;
        }
        .m-sidebar.open { transform: translateX(0); }

        .m-overlay { position: fixed; top: 0; right: 0; bottom: 0; left: 0; z-index: 30; background: rgba(0,0,0,.45); }

        .m-bottomnav {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 40;
            display: flex; width: 100%;
        }
        .m-bottomnav a {
            flex: 1 1 0; min-width: 0;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 3px; padding: 8px 2px; font-size: 10px; font-weight: 500; text-align: center;
        }

        .m-menu-btn { display: inline-flex; }

        @media (min-width: 1024px) {
            body.m-body { padding-left: 16rem; padding-bottom: 0; }
            .m-sidebar { transform: none; }
            .m-bottomnav, .m-menu-btn, .m-overlay { display: none !important; }
        }
    </style>
    @stack('head')
</head>
<body class="m-body bg-[#FAF6F5] font-sans text-stone-800" x-data="{ sidebar: false }">

    {{-- Overlay mobile --}}
    <div class="m-overlay" x-cloak x-show="sidebar" @click="sidebar = false"></div>

    {{-- Sidebar --}}
    <aside class="m-sidebar bg-maroon-dark text-white" :class="{ 'open': sidebar }">
        <div class="flex items-center gap-3 border-b border-white/10 px-6 py-5">
            <x-logo tinggi="h-9" />
            <div class="leading-tight">
                <p class="font-serif text-base font-bold">SI HIMA</p>
                <p class="text-xs text-white/60">Portal Mahasiswa</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @foreach($nav as $item)
                @php $aktif = request()->routeIs($item['match'] ?? $item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                          {{ $aktif ? 'bg-white text-maroon' : 'text-white/80 hover:bg-white/10' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="mb-3 flex items-center gap-3">
                @if($anggota?->foto)
                    <img src="{{ Storage::url($anggota->foto) }}" class="h-9 w-9 rounded-full object-cover" alt="">
                @else
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-sm font-semibold">{{ $inisial }}</div>
                @endif
                <div class="min-w-0 leading-tight">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="font-mono text-xs text-white/60">{{ auth()->user()->nim }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('mahasiswa.logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-white/10 px-3 py-2 text-sm font-medium transition hover:bg-white/20">
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Header --}}
    <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-stone-200 bg-white/90 px-4 py-3 backdrop-blur sm:px-8">
        <button type="button" class="m-menu-btn rounded-lg p-2 text-stone-600 hover:bg-stone-100"
                @click="sidebar = true" aria-label="Buka menu">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
            </svg>
        </button>
        <h1 class="font-serif text-xl font-bold text-maroon">@yield('title', 'Portal')</h1>
        <div class="ml-auto hidden text-sm text-stone-500 sm:block">
            {{ now()->locale('id')->translatedFormat('l, d F Y') }}
        </div>
    </header>

    <main class="px-4 py-6 sm:px-8">
        @yield('content')
    </main>

    {{-- Bottom nav (mobile) --}}
    <nav class="m-bottomnav border-t border-stone-200 bg-white">
        @foreach($nav as $item)
            @php $aktif = request()->routeIs($item['match'] ?? $item['route']); @endphp
            <a href="{{ route($item['route']) }}" class="{{ $aktif ? 'text-maroon' : 'text-stone-400' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                {{ $item['short'] }}
            </a>
        @endforeach
    </nav>

    {{-- Toast --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4500)"
             x-show="show" x-cloak x-transition
             style="position:fixed;right:1rem;top:1rem;z-index:60;max-width:22rem;"
             class="rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg {{ session('success') ? 'bg-emerald-700' : 'bg-red-700' }}">
            <div class="flex items-start gap-3">
                <span class="flex-1">{{ session('success') ?? session('error') }}</span>
                <button type="button" @click="show = false" class="text-white/70 hover:text-white">&times;</button>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>
