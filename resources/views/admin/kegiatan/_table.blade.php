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
                <th class="px-5 py-3">Tipe</th>
                <th class="px-5 py-3">Sesi</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse ($kegiatan as $k)
                @php
                    $mulai   = $k->tanggal_mulai->locale('id');
                    $selesai = $k->tanggal_selesai->locale('id');
                @endphp
                <tr class="hover:bg-stone-50">
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.kegiatan.show', $k) }}" class="font-medium text-stone-800 hover:text-maroon">{{ $k->nama }}</a>
                        @if ($k->lokasi)
                            <p class="text-xs text-stone-400">{{ $k->lokasi }}</p>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-3 text-stone-600">
                        {{ $mulai->translatedFormat('d M Y') }}
                        @if (!$mulai->isSameDay($selesai))
                            – {{ $selesai->translatedFormat('d M Y') }}
                        @endif
                    </td>
                    <td class="px-5 py-3 capitalize text-stone-600">{{ $k->tipe }}</td>
                    <td class="px-5 py-3 text-stone-600">{{ $k->sesi_absensi_count }}</td>
                    <td class="px-5 py-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$k->status][1] }}">{{ $badge[$k->status][0] }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-3 text-xs font-semibold">
                            <a href="{{ route('admin.kegiatan.show', $k) }}" class="text-maroon hover:underline">Detail</a>
                            <a href="{{ route('admin.kegiatan.edit', $k) }}" class="text-stone-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.kegiatan.destroy', $k) }}"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus kegiatan ' . $k->nama . '?') }})">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-stone-400">Belum ada kegiatan.</td>
                </tr>
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
