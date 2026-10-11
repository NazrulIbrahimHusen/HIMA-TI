@php
    $label = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];
    $warna = [
        'hadir' => 'bg-emerald-100 text-emerald-800',
        'izin'  => 'bg-sky-100 text-sky-800',
        'sakit' => 'bg-amber-100 text-amber-800',
        'alpa'  => 'bg-red-100 text-red-800',
    ];
    $multi = $sesi->count() > 1;
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
            <tr>
                <th class="px-5 py-3">No</th>
                <th class="px-5 py-3">NIM</th>
                <th class="px-5 py-3">Nama</th>
                <th class="px-5 py-3">Peserta</th>
                @foreach ($sesi as $s)
                    <th class="whitespace-nowrap px-5 py-3">{{ $s->nama_sesi }}</th>
                @endforeach
                @if ($multi)
                    <th class="px-5 py-3">Kehadiran</th>
                @endif
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse ($baris as $b)
                <tr class="hover:bg-stone-50">
                    <td class="px-5 py-3 text-stone-500">{{ $baris->firstItem() + $loop->index }}</td>
                    <td class="px-5 py-3 font-mono text-xs">{{ $b['nim'] }}</td>
                    <td class="px-5 py-3 font-medium">{{ $b['nama'] }}</td>
                    <td class="px-5 py-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $b['tipe'] === 'anggota' ? 'bg-maroon-soft text-maroon' : 'bg-amber-100 text-amber-800' }}">
                            {{ $b['tipe'] === 'anggota' ? 'Anggota' : 'Tamu' }}
                        </span>
                    </td>
                    @foreach ($sesi as $s)
                        @php $st = $b['status'][$s->id] ?? null; @endphp
                        <td class="px-5 py-3">
                            @if ($st)
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $warna[$st] }}">{{ $label[$st] }}</span>
                            @else
                                <span class="text-stone-300">—</span>
                            @endif
                        </td>
                    @endforeach
                    @if ($multi)
                        <td class="whitespace-nowrap px-5 py-3">
                            @if ($b['hadir_semua'])
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Hadir</span>
                            @else
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">Tidak hadir</span>
                                <span class="ml-1 font-mono text-xs text-stone-400">{{ $b['jumlah_hadir'] }}/{{ $sesi->count() }}</span>
                            @endif
                        </td>
                    @endif
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.absensi.peserta', [$kegiatan, $b['nim']]) }}" class="text-xs font-semibold text-maroon hover:underline">Ubah</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 5 + $sesi->count() + ($multi ? 1 : 0) }}" class="px-5 py-12 text-center text-stone-400">
                        Belum ada data absensi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 px-5 py-3">
    <p class="text-xs text-stone-500">
        Menampilkan {{ $baris->firstItem() ?? 0 }}–{{ $baris->lastItem() ?? 0 }} dari {{ $baris->total() }} peserta
    </p>
    @if ($baris->hasPages())
        <div>{{ $baris->links() }}</div>
    @endif
</div>
