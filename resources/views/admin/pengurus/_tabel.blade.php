<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="text-xs uppercase tracking-wide text-stone-400">
            <tr>
                <th class="px-5 py-2">Urutan</th>
                <th class="px-5 py-2">Nama</th>
                <th class="px-5 py-2">NIM</th>
                <th class="px-5 py-2">Jabatan</th>
                <th class="px-5 py-2 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse ($daftar as $p)
                <tr class="hover:bg-stone-50">
                    <td class="px-5 py-3 font-mono text-xs text-stone-500">{{ $p->urutan }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            @if ($p->anggota?->foto)
                                <img src="{{ asset('storage/' . $p->anggota->foto) }}" alt="" class="h-8 w-8 rounded-full object-cover">
                            @else
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-maroon-soft text-xs font-semibold text-maroon">{{ strtoupper(mb_substr($p->anggota?->nama ?? '?', 0, 1)) }}</div>
                            @endif
                            <span class="font-medium">{{ $p->anggota?->nama ?? '(anggota dihapus)' }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-stone-500">{{ $p->anggota?->nim }}</td>
                    <td class="px-5 py-3">
                        <span class="rounded-full bg-maroon-soft px-2.5 py-1 text-xs font-semibold text-maroon">{{ $p->jabatan }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-3 text-xs font-semibold">
                            <a href="{{ route('admin.pengurus.edit', $p) }}" class="text-maroon hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.pengurus.destroy', $p) }}"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus ' . ($p->anggota?->nama ?? 'pengurus ini') . ' dari struktur?') }})">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-6 text-center text-sm text-stone-400">Belum ada pengurus.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
