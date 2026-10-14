@php
    // route => null artinya modul belum dibuat (tampil "Segera")
    $menu = [
        ['label' => 'Dashboard',  'route' => 'admin.dashboard', 'icon' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['label' => 'Anggota',    'route' => 'admin.anggota.index', 'match' => 'admin.anggota.*', 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
        ['label' => 'Pengurus',   'route' => null, 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['label' => 'Kegiatan',   'route' => 'admin.kegiatan.index', 'match' => 'admin.kegiatan.*', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
        ['label' => 'Absensi',    'route' => 'admin.absensi.index', 'match' => 'admin.absensi.*', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Monitoring', 'route' => null, 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
        ['label' => 'Pengumuman', 'route' => null, 'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0'],
    ];

    if (auth()->user()->isSuperAdmin()) {
        $menu[] = ['label' => 'Kelola Admin', 'route' => null, 'icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'];
    }

    $roleLabel = auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Admin';
    $inisial   = strtoupper(mb_substr(auth()->user()->name, 0, 1));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SI HIMA Informatika</title>

    @include('layouts._tema')
    @stack('head')
</head>
<body class="bg-[#FAF6F5] font-sans text-stone-800" x-data="{ sidebar: false }">

    {{-- Overlay mobile --}}
    <div x-cloak x-show="sidebar" @click="sidebar = false"
         class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-maroon-dark text-white transition-transform duration-200 lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            <x-logo tinggi="h-9" />
            <div class="leading-tight">
                <p class="font-serif text-base font-bold">SI HIMA</p>
                <p class="text-xs text-white/60">Informatika UNILAM</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @foreach ($menu as $item)
                @php $aktif = $item['route'] && request()->routeIs($item['match'] ?? $item['route']); @endphp
                @if ($item['route'])
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                              {{ $aktif ? 'bg-white text-maroon' : 'text-white/80 hover:bg-white/10' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @else
                    <div class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-white/40">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        <span class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] uppercase tracking-wide">Segera</span>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-sm font-semibold">{{ $inisial }}</div>
                <div class="min-w-0 leading-tight">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-white/60">{{ $roleLabel }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full rounded-lg bg-white/10 px-3 py-2 text-sm font-medium transition hover:bg-white/20">
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Konten utama --}}
    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-stone-200 bg-white/90 px-4 py-3 backdrop-blur sm:px-8">
            <button @click="sidebar = true" class="rounded-lg p-2 text-stone-600 hover:bg-stone-100 lg:hidden" aria-label="Buka menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                </svg>
            </button>
            <h1 class="font-serif text-xl font-bold text-maroon">@yield('title', 'Dashboard')</h1>
            <div class="ml-auto hidden text-sm text-stone-500 sm:block">
                {{ now()->locale('id')->translatedFormat('l, d F Y') }}
            </div>
        </header>

        <main class="px-4 py-6 sm:px-8">
            @yield('content')
        </main>
    </div>

    {{-- Toast notification --}}
    @if (session('success') || session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4500)"
             x-show="show" x-cloak x-transition
             class="fixed right-4 top-4 z-50 max-w-sm rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg
                    {{ session('success') ? 'bg-emerald-700' : 'bg-red-700' }}">
            <div class="flex items-start gap-3">
                <span class="flex-1">{{ session('success') ?? session('error') }}</span>
                <button @click="show = false" class="text-white/70 hover:text-white" aria-label="Tutup">&times;</button>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>
