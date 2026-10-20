@extends('layouts.admin')

@section('title', 'Detail Keaktifan')

@section('content')
    @php
        $namaStatus = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];
        $warna  = [
            'hadir' => 'bg-emerald-100 text-emerald-800',
            'izin'  => 'bg-sky-100 text-sky-800',
            'sakit' => 'bg-amber-100 text-amber-800',
            'alpa'  => 'bg-red-100 text-red-800',
        ];
    @endphp

    <a href="{{ route('admin.monitoring.index', ['rentang' => $kode]) }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke monitoring</a>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            @if ($anggota->foto)
                <img src="{{ asset('storage/' . $anggota->foto) }}" alt="" class="h-16 w-16 rounded-full object-cover">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-maroon-soft font-serif text-2xl font-bold text-maroon">{{ strtoupper(mb_substr($anggota->nama, 0, 1)) }}</div>
            @endif
            <div>
                <h2 class="font-serif text-xl font-bold text-stone-800">{{ $anggota->nama }}</h2>
                <p class="text-sm text-stone-500"><span class="font-mono">{{ $anggota->nim }}</span> · {{ $anggota->prodi }} · Angkatan {{ $anggota->angkatan }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.monitoring.anggota', $anggota) }}" class="flex items-center gap-2">
            <label for="rentang" class="text-sm text-stone-500">Rentang</label>
            <select id="rentang" name="rentang" onchange="this.form.submit()" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                @foreach ($opsiRentang as $k => $teks)
                    <option value="{{ $k }}" @selected((string) $kode === (string) $k)>{{ $teks }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-stone-500">Persentase Kehadiran</p>
            <p class="mt-2 font-serif text-3xl font-bold text-maroon">{{ is_null($riwayat['persen']) ? '-' : $riwayat['persen'] . '%' }}</p>
            <p class="mt-1 text-xs text-stone-400">{{ ucfirst($label) }}</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-stone-500">Kegiatan Dihadiri</p>
            <p class="mt-2 font-serif text-3xl font-bold text-maroon">{{ $riwayat['hadir'] }} <span class="text-lg text-stone-400">/ {{ $riwayat['total'] }}</span></p>
            <p class="mt-1 text-xs text-stone-400">Hadir di semua sesi kegiatan</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-stone-500">Grade</p>
            <div class="mt-2"><x-grade :nilai="$riwayat['grade']" class="!h-10 !w-10 !text-xl" /></div>
        </div>
    </div>

    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-200 px-5 py-4">
            <h3 class="font-serif text-lg font-bold text-stone-800">Riwayat Kegiatan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
                    <tr>
                        <th class="px-5 py-3">Kegiatan</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Status per sesi</th>
                        <th class="px-5 py-3">Kehadiran</th>
                        <th class="px-5 py-3 text-right">Ubah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($riwayat['baris'] as $b)
                        @php $k = $b['kegiatan']; @endphp
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-3 font-medium">{{ $k->nama }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-stone-600">{{ $k->tanggal_mulai->locale('id')->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($b['status'] as $s)
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $warna[$s['status']] }}" title="{{ $s['sesi'] }}">
                                            {{ count($b['status']) > 1 ? $s['sesi'] . ': ' : '' }}{{ $namaStatus[$s['status']] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $b['hadir'] ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $b['hadir'] ? 'Hadir' : 'Tidak hadir' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.absensi.peserta', [$k, $anggota->nim]) }}" class="text-xs font-semibold text-maroon hover:underline">Ubah status</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-stone-400">Belum ada data kehadiran pada rentang ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
