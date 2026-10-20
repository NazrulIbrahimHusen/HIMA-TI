@extends('layouts.mahasiswa')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-5 max-w-2xl mx-auto lg:max-w-none">

    {{-- Kartu sambutan --}}
    <div class="rounded-2xl bg-gradient-to-br from-[#6B1124] to-[#4A0012] p-5 text-white shadow-sm">
        <p class="text-sm text-white/70">Selamat datang,</p>
        <h1 class="mt-0.5 font-serif text-2xl font-bold">{{ $user->name }}</h1>
        <p class="font-mono text-sm text-white/60">NIM {{ $user->nim }}</p>
        @if($anggota)
            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                <span class="rounded-full bg-white/15 px-2.5 py-1">{{ $anggota->prodi }}</span>
                <span class="rounded-full bg-white/15 px-2.5 py-1">Angkatan {{ $anggota->angkatan }}</span>
                <span class="rounded-full {{ $anggota->status === 'aktif' ? 'bg-emerald-400/20 text-emerald-200' : 'bg-red-400/20 text-red-200' }} px-2.5 py-1 font-semibold">
                    {{ ucfirst($anggota->status) }}
                </span>
            </div>
        @endif
    </div>

    {{-- Statistik keaktifan --}}
    @if($anggota)
    <div class="grid grid-cols-3 gap-3">
        {{-- Grade --}}
        <div class="rounded-xl border border-stone-200 bg-white p-4 text-center shadow-sm">
            @if($riwayat['grade'])
                <p class="font-serif text-4xl font-bold text-[#6B1124]">{{ $riwayat['grade'] }}</p>
                <p class="mt-1 text-xs text-stone-500">Grade</p>
                <p class="text-[10px] text-stone-400">{{ $riwayat['persen'] }}%</p>
            @else
                <p class="font-serif text-4xl font-bold text-stone-300">—</p>
                <p class="mt-1 text-xs text-stone-400">Belum ada data</p>
            @endif
        </div>

        {{-- Hadir --}}
        <div class="rounded-xl border border-stone-200 bg-white p-4 text-center shadow-sm">
            <p class="font-serif text-4xl font-bold text-emerald-600">{{ $riwayat['hadir'] }}</p>
            <p class="mt-1 text-xs text-stone-500">Hadir</p>
            <p class="text-[10px] text-stone-400">dari {{ $riwayat['total'] }} kegiatan</p>
        </div>

        {{-- Tidak hadir --}}
        <div class="rounded-xl border border-stone-200 bg-white p-4 text-center shadow-sm">
            <p class="font-serif text-4xl font-bold text-red-500">{{ $riwayat['total'] - $riwayat['hadir'] }}</p>
            <p class="mt-1 text-xs text-stone-500">Tidak Hadir</p>
            <p class="text-[10px] text-stone-400">6 bulan terakhir</p>
        </div>
    </div>
    @endif

    {{-- Kegiatan mendatang --}}
    @if($mendatang->isNotEmpty())
    <div>
        <h2 class="mb-3 font-serif text-base font-bold text-stone-800">Kegiatan Mendatang</h2>
        <div class="space-y-2">
            @foreach($mendatang as $k)
            <div class="flex items-center gap-3 rounded-xl border border-stone-200 bg-white p-3.5 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg bg-[#FBF4F3] text-[#6B1124]">
                    <span class="text-xs font-bold leading-none">{{ $k->tanggal_mulai->locale('id')->translatedFormat('d') }}</span>
                    <span class="text-[10px] uppercase">{{ $k->tanggal_mulai->locale('id')->translatedFormat('M') }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-stone-800">{{ $k->nama }}</p>
                    <p class="text-xs text-stone-500">{{ $k->lokasi }}</p>
                </div>
                @if($k->sertifikat_aktif)
                    <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Sertifikat</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Riwayat terakhir --}}
    @if($terakhir->isNotEmpty())
    <div>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-serif text-base font-bold text-stone-800">Kehadiran Terakhir</h2>
            <a href="{{ route('mahasiswa.absensi') }}" class="text-xs font-medium text-[#6B1124] hover:underline">Lihat semua</a>
        </div>
        <div class="space-y-2">
            @foreach($terakhir as $b)
            <div class="flex items-center gap-3 rounded-xl border border-stone-200 bg-white p-3.5 shadow-sm">
                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-semibold
                    {{ $b['hadir'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                    {{ $b['hadir'] ? 'Hadir' : 'Tidak Hadir' }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-stone-800">{{ $b['kegiatan']->nama }}</p>
                    <p class="text-xs text-stone-400">{{ $b['kegiatan']->tanggal_mulai->locale('id')->translatedFormat('d M Y') }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Shortcut --}}
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('mahasiswa.kta') }}"
           class="flex items-center gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm hover:bg-[#FDF8F7] transition">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#FBF4F3]">
                <svg class="h-5 w-5 text-[#6B1124]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-stone-800">KTA Digital</span>
        </a>
        <a href="{{ route('mahasiswa.monitoring') }}"
           class="flex items-center gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm hover:bg-[#FDF8F7] transition">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#FBF4F3]">
                <svg class="h-5 w-5 text-[#6B1124]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-stone-800">Monitoring</span>
        </a>
    </div>

</div>
@endsection
