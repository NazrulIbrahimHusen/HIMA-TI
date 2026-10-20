<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
            <tr>
                <th class="px-5 py-3">Judul</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Terbit</th>
                <th class="px-5 py-3">Penulis</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse ($pengumuman as $p)
                <tr class="hover:bg-stone-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-stone-800">{{ $p->judul }}</p>
                        <p class="max-w-md truncate text-xs text-stone-400">{{ \Illuminate\Support\Str::limit(strip_tags($p->konten), 90) }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $p->isPublished() ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' }}">
                            {{ $p->isPublished() ? 'Terbit' : 'Draft' }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-5 py-3 text-stone-500">{{ $p->published_at ? $p->published_at->locale('id')->translatedFormat('d M Y, H:i') : '—' }}</td>
                    <td class="px-5 py-3 text-stone-600">{{ $p->postedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-3 text-xs font-semibold">
                            @if ($p->isPublished())
                                <a href="{{ route('publik.pengumuman.show', $p) }}" target="_blank" class="text-stone-600 hover:underline">Lihat</a>
                            @endif
                            <a href="{{ route('admin.pengumuman.edit', $p) }}" class="text-maroon hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.pengumuman.destroy', $p) }}"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus pengumuman ' . $p->judul . '?') }})">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-stone-400">Belum ada pengumuman.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 px-5 py-3">
    <p class="text-xs text-stone-500">Menampilkan {{ $pengumuman->firstItem() ?? 0 }}–{{ $pengumuman->lastItem() ?? 0 }} dari {{ $pengumuman->total() }} pengumuman</p>
    @if ($pengumuman->hasPages())
        <div>{{ $pengumuman->links() }}</div>
    @endif
</div>
