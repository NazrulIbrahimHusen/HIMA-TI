<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Mahasiswa — SI HIMA Informatika</title>
    @include('layouts._tema')
</head>
<body class="min-h-screen bg-[#FAF6F5] text-stone-800">

    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4500)" x-show="show" x-cloak x-transition
             class="fixed right-4 top-4 z-50 max-w-sm rounded-xl bg-emerald-700 px-4 py-3 text-sm font-medium text-white shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    <header class="bg-[#4A0012] text-white">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4">
            <div class="flex items-center gap-3">
                <x-logo tinggi="h-8" />
                <div class="leading-tight">
                    <p class="serif font-bold">SI HIMA Informatika</p>
                    <p class="text-xs text-white/60">Portal Mahasiswa</p>
                </div>
            </div>
            <form method="POST" action="{{ route('mahasiswa.logout') }}">
                @csrf
                <button class="rounded-lg bg-white/10 px-3 py-2 text-sm font-medium hover:bg-white/20">Keluar</button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-8">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-stone-500">Selamat datang,</p>
            <h1 class="serif mt-1 text-2xl font-bold text-[#6B1124]">{{ auth()->user()->name }}</h1>
            <p class="mono mt-1 text-sm text-stone-500">NIM {{ auth()->user()->nim }}</p>
        </div>

        <div class="mt-4 rounded-2xl border border-dashed border-stone-300 bg-white/60 p-6 text-center text-sm text-stone-500">
            Fitur portal (kegiatan, status absensi, monitoring keaktifan, KTA digital, dan sertifikat)
            sedang dalam pengembangan.
        </div>
    </main>
</body>
</html>
