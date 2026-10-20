@extends('layouts.mahasiswa')

@section('title', 'Status Absensi')

@section('content')
<div class="mx-auto max-w-2xl space-y-5 lg:max-w-none">

    {{-- Filter --}}
    <form method="GET" action="{{ route('mahasiswa.absensi') }}"
          class="flex flex-wrap gap-2 rounded-xl border border-stone-200 bg-white p-4 shadow-sm">

        <select name="rentang"
                class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-[#6B1124] focus:outline-none focus:ring-1 focus:ring-[#6B1124]/30">
            @foreach($opsiRentang as $kode => $label)
                <option value="{{ $kode }}" {{ request('rentang', 'semester') === $kode ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select name="status"
                class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-[#6B1124] focus:outline-none focus:ring-1 focus:ring-[#6B1124]/30">
            <option value="" {{ !request('status') ? 'selected' : '' }}>Semua status</option>
            <option value="hadir"       {{ request('status') === 'hadir'       ? 'selected' : '' }}>Hadir</option>
            <option value="tidak_hadir" {{ request('status') === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
        </select>

        <button type="submit"
                class="rounded-lg bg-[#6B1124] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4A0012] transition">
            Terapkan
        </button>
        @if(request('rentang') || request('status'))
            <a href="{{ route('mahasiswa.absensi') }}"
               class="rounded-lg border border-stone-300 px-4 py-2 text-sm text-stone-600 hover:bg-stone-50 transition">
                Reset
            </a>
        @endif
    </form>

    {{-- Ringkasan --}}
    @if($ringkasan && $ringkasan['total'] > 0)
    <div class="grid grid-cols-3 gap-3">
        <div class="rounded-xl border border-stone-200 bg-white p-4 text-center shadow-sm">
            @if($ringkasan['grade'])
                <p class="font-serif text-3xl font-bold text-[#6B1124]">{{ $ringkasan['grade'] }}</p>
                <p class="text-xs text-stone-500">Grade</p>
            @else
                <p class="font-serif text-3xl font-bold text-stone-300">—</p>
                <p class="text-xs text-stone-400">Belum dinilai</p>
            @endif
        </div>
        <div class="rounded-xl border border-stone-200 bg-white p-4 text-center shadow-sm">
            <p class="font-serif text-3xl font-bold text-emerald-600">{{ $ringkasan['hadir'] }}</p>
            <p class="text-xs text-stone-500">Hadir</p>
        </div>
        <div class="rounded-xl border border-stone-200 bg-white p-4 text-center shadow-sm">
            <p class="font-serif text-3xl font-bold text-stone-700">{{ $ringkasan['total'] }}</p>
            <p class="text-xs text-stone-500">Total Kegiatan</p>
        </div>
    </div>

    {{-- Progress bar --}}
    @if($ringkasan['persen'] !== null)
    <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
        <div class="mb-2 flex items-center justify-between text-sm">
            <span class="font-medium text-stone-700">Tingkat Kehadiran</span>
            <span class="font-bold text-[#6B1124]">{{ $ringkasan['persen'] }}%</span>
        </div>
        <div class="h-2.5 w-full overflow-hidden rounded-full bg-stone-100">
            <div class="h-full rounded-full transition-all duration-500
                        {{ $ringkasan['persen'] >= 80 ? 'bg-emerald-500' : ($ringkasan['persen'] >= 60 ? 'bg-blue-500' : ($ringkasan['persen'] >= 40 ? 'bg-amber-500' : 'bg-red-500')) }}"
                 style="width: {{ $ringkasan['persen'] }}%">
            </div>
        </div>
        <p class="mt-1.5 text-xs text-stone-400">{{ $rentang['label'] }}</p>
    </div>
    @endif
    @endif

    {{-- Daftar kegiatan --}}
    <div class="rounded-xl border border-stone-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-stone-100 px-5 py-3.5">
            <h2 class="font-serif text-base font-bold text-stone-800">Riwayat Kegiatan</h2>
        </div>

        @if($baris->isEmpty())
            <div class="flex flex-col items-center justify-center gap-2 py-12 text-stone-400">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <p class="text-sm">Belum ada data kehadiran.</p>
            </div>
        @else
            <ul class="divide-y divide-stone-100">
                @foreach($baris as $b)
                <li class="flex items-start gap-4 px-5 py-4 hover:bg-[#FDF8F7] transition">
                    {{-- Ikon tanggal --}}
                    <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg bg-[#FBF4F3] text-[#6B1124]">
                        <span class="text-xs font-bold leading-none">{{ $b['kegiatan']->tanggal_mulai->format('d') }}</span>
                        <span class="text-[10px] uppercase">{{ $b['kegiatan']->tanggal_mulai->locale('id')->translatedFormat('M') }}</span>
                    </div>

                    {{-- Info kegiatan --}}
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-stone-800">{{ $b['kegiatan']->nama }}</p>
                        <p class="text-xs text-stone-400">{{ $b['kegiatan']->tanggal_mulai->locale('id')->translatedFormat('d M Y') }}</p>

                        {{-- Status tiap sesi --}}
                        @if(count($b['status']) > 0)
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($b['status'] as $sesi)
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                                    {{ $sesi['status'] === 'hadir' ? 'bg-emerald-50 text-emerald-700' :
                                       ($sesi['status'] === 'izin' ? 'bg-blue-50 text-blue-700' :
                                       ($sesi['status'] === 'sakit' ? 'bg-amber-50 text-amber-700' :
                                       'bg-red-50 text-red-600')) }}">
                                    {{ $sesi['sesi'] }}: {{ ucfirst($sesi['status']) }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Badge hadir/tidak --}}
                    <span class="shrink-0 mt-0.5 rounded-full px-2.5 py-0.5 text-[11px] font-bold
                        {{ $b['hadir'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                        {{ $b['hadir'] ? '✓ Hadir' : '✗ Tidak' }}
                    </span>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>
@endsection
