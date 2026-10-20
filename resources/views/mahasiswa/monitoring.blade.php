@extends('layouts.mahasiswa')

@section('title', 'Monitoring Keaktifan')

@section('content')
<div class="mx-auto max-w-2xl space-y-5 lg:max-w-none">

    {{-- Filter rentang --}}
    <form method="GET" action="{{ route('mahasiswa.monitoring') }}"
          class="flex flex-wrap gap-2 rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
        <select name="rentang"
                class="flex-1 rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-[#6B1124] focus:outline-none focus:ring-1 focus:ring-[#6B1124]/30">
            @foreach($opsiRentang as $kode => $label)
                <option value="{{ $kode }}" {{ request('rentang', 'semester') === $kode ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="rounded-lg bg-[#6B1124] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4A0012] transition">
            Terapkan
        </button>
    </form>

    @if(!$riwayat || $riwayat['total'] === 0)
        <div class="rounded-xl border border-stone-200 bg-white p-10 text-center shadow-sm">
            <p class="text-stone-400 text-sm">Belum ada data kehadiran pada periode ini.</p>
        </div>
    @else

    {{-- Kartu grade utama --}}
    <div class="rounded-2xl bg-gradient-to-br from-[#6B1124] to-[#4A0012] p-5 text-white shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-white/70">Grade Keaktifan</p>
                <p class="mt-1 font-serif text-6xl font-bold tracking-tight">{{ $riwayat['grade'] ?? '—' }}</p>
                @php
                    $gradeInfo = $riwayat['grade'] ? ($grade[$riwayat['grade']] ?? null) : null;
                @endphp
                @if($gradeInfo)
                    <p class="mt-1 text-sm text-white/80">{{ $gradeInfo['label'] }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="font-serif text-4xl font-bold">{{ $riwayat['persen'] ?? 0 }}%</p>
                <p class="text-xs text-white/60">{{ $riwayat['hadir'] }} dari {{ $riwayat['total'] }} kegiatan</p>
                <p class="mt-1 text-xs text-white/50">{{ $rentang['label'] }}</p>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-white/20">
            <div class="h-full rounded-full bg-white transition-all duration-700"
                 style="width: {{ $riwayat['persen'] ?? 0 }}%"></div>
        </div>
    </div>

    {{-- Keterangan grade --}}
    <div class="grid grid-cols-5 gap-2">
        @foreach($grade as $huruf => $info)
        <div class="rounded-lg border p-2 text-center text-xs
            {{ ($riwayat['grade'] ?? null) === $huruf
                ? 'border-[#6B1124] bg-[#6B1124] text-white shadow-sm'
                : 'border-stone-200 bg-white text-stone-500' }}">
            <p class="font-serif text-lg font-bold">{{ $huruf }}</p>
            <p class="leading-tight">{{ $info['rentang'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Grafik per bulan --}}
    @if($perBulan->isNotEmpty())
    <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 font-serif text-base font-bold text-stone-800">Kehadiran per Bulan</h2>
        <div class="flex items-end gap-2 h-36">
            @foreach($perBulan as $bulan)
            @php
                $pct = $bulan['total'] > 0 ? round($bulan['hadir'] / $bulan['total'] * 100) : 0;
                $tinggi = max(8, $pct);
            @endphp
            <div class="flex flex-1 flex-col items-center gap-1">
                <span class="text-[10px] text-stone-500 font-medium">{{ $pct }}%</span>
                <div class="w-full rounded-t-md transition-all duration-500
                    {{ $pct >= 80 ? 'bg-emerald-500' : ($pct >= 60 ? 'bg-blue-500' : ($pct >= 40 ? 'bg-amber-500' : 'bg-red-400')) }}"
                     style="height: {{ $tinggi }}%">
                </div>
                <span class="text-[9px] text-stone-400 text-center leading-tight">{{ $bulan['label'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="mt-3 flex items-center gap-3 text-[10px] text-stone-500">
            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-emerald-500 inline-block"></span> ≥80%</span>
            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-blue-500 inline-block"></span> 60–79%</span>
            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-amber-500 inline-block"></span> 40–59%</span>
            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-red-400 inline-block"></span> &lt;40%</span>
        </div>
    </div>
    @endif

    {{-- Riwayat per kegiatan --}}
    <div class="rounded-xl border border-stone-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-stone-100 px-5 py-3.5">
            <h2 class="font-serif text-base font-bold text-stone-800">Detail Kegiatan</h2>
        </div>
        <ul class="divide-y divide-stone-100">
            @foreach($riwayat['baris'] as $b)
            <li class="flex items-start gap-4 px-5 py-3.5 hover:bg-[#FDF8F7] transition">
                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold
                    {{ $b['hadir'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                    {{ $b['hadir'] ? '✓' : '✗' }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-stone-800">{{ $b['kegiatan']->nama }}</p>
                    <p class="text-xs text-stone-400">{{ $b['kegiatan']->tanggal_mulai->locale('id')->translatedFormat('d M Y') }}</p>
                    @if(count($b['status']) > 1)
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach($b['status'] as $sesi)
                                <span class="text-[10px] rounded-full px-1.5 py-0.5
                                    {{ $sesi['status'] === 'hadir' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                                    {{ $sesi['sesi'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    @endif

</div>
@endsection
