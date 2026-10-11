@php
    $badge = [
        'draft'     => ['Draft',          'bg-stone-200 text-stone-600'],
        'published' => ['Dipublikasikan', 'bg-emerald-100 text-emerald-800'],
        'closed'    => ['Selesai',        'bg-red-100 text-red-800'],
    ];
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
            <tr>
                <th class="px-5 py-3">Kegiatan</th>
                <th class="px-5 py-3">Tanggal</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Sesi</th>
                <th class="px-5 py-3">Peserta</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse ($kegiatan as $k)
                <tr class="hover:bg-stone-50">
                    <td class="px-5 py-3 font-medium">{{ $k->nama }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-stone-600">{{ $k->tanggal_mulai->locale('id')->translatedFormat('d M Y') }}</td>
                    <td class="px-5 py-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$k->status][1] }}">{{ $badge[$k->status][0] }}</span>
                    </td>
                    <td class="px-5 py-3 text-stone-600">{{ $k->sesi_absensi_count }}</td>
                    <td class="px-5 py-3 text-stone-600">{{ $peserta[$k->id] ?? 0 }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.absensi.kegiatan', $k) }}"
                           class="rounded-lg bg-maroon px-3 py-1.5 text-xs font-semibold text-white hover:bg-maroon-dark">Kelola Absensi</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-stone-400">Belum ada kegiatan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 px-5 py-3">
    <p class="text-xs text-stone-500">
        Menampilkan {{ $kegiatan->firstItem() ?? 0 }}–{{ $kegiatan->lastItem() ?? 0 }} dari {{ $kegiatan->total() }} kegiatan
    </p>
    @if ($kegiatan->hasPages())
        <div>{{ $kegiatan->links() }}</div>
    @endif
</div>
