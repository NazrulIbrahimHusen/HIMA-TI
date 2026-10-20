<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
            <tr>
                <th class="px-5 py-3">No</th>
                <th class="px-5 py-3">Anggota</th>
                <th class="px-5 py-3">Prodi</th>
                <th class="px-5 py-3">Kehadiran</th>
                <th class="px-5 py-3">Persentase</th>
                <th class="px-5 py-3">Grade</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse ($baris as $b)
                @php $a = $b['anggota']; @endphp
                <tr class="hover:bg-stone-50">
                    <td class="px-5 py-3 text-stone-500">{{ $baris->firstItem() + $loop->index }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            @if ($a->foto)
                                <img src="{{ asset('storage/' . $a->foto) }}" alt="" class="h-9 w-9 rounded-full object-cover">
                            @else
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-maroon-soft text-sm font-semibold text-maroon">{{ strtoupper(mb_substr($a->nama, 0, 1)) }}</div>
                            @endif
                            <div>
                                <p class="font-medium">{{ $a->nama }}</p>
                                <p class="font-mono text-xs text-stone-400">{{ $a->nim }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-stone-600">{{ $a->prodi }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-stone-600">
                        @if ($b['total'] > 0)
                            {{ $b['hadir'] }} dari {{ $b['total'] }} kegiatan
                        @else
                            <span class="text-stone-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if (!is_null($b['persen']))
                            <div class="flex items-center gap-3">
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-stone-200">
                                    <div class="h-2 rounded-full bg-maroon" style="width: {{ min($b['persen'], 100) }}%"></div>
                                </div>
                                <span class="font-mono text-xs text-stone-600">{{ $b['persen'] }}%</span>
                            </div>
                        @else
                            <span class="text-stone-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3"><x-grade :nilai="$b['grade']" /></td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.monitoring.anggota', [$a, 'rentang' => $kode]) }}" class="text-xs font-semibold text-maroon hover:underline">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-stone-400">Tidak ada anggota yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 px-5 py-3">
    <p class="text-xs text-stone-500">
        Menampilkan {{ $baris->firstItem() ?? 0 }}–{{ $baris->lastItem() ?? 0 }} dari {{ $baris->total() }} anggota
    </p>
    @if ($baris->hasPages())
        <div>{{ $baris->links() }}</div>
    @endif
</div>
