<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
            <tr>
                <th class="px-5 py-3">Anggota</th>
                <th class="px-5 py-3">NIM</th>
                <th class="px-5 py-3">Prodi</th>
                <th class="px-5 py-3">Angkatan</th>
                <th class="px-5 py-3">Smt</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse ($anggota as $a)
                <tr class="hover:bg-stone-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            @if ($a->foto)
                                <img src="{{ asset('storage/' . $a->foto) }}" alt="" class="h-9 w-9 rounded-full object-cover">
                            @else
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-maroon-soft text-sm font-semibold text-maroon">
                                    {{ strtoupper(mb_substr($a->nama, 0, 1)) }}
                                </div>
                            @endif
                            <span class="font-medium">{{ $a->nama }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 font-mono text-xs">{{ $a->nim }}</td>
                    <td class="px-5 py-3 text-stone-600">{{ $a->prodi }}</td>
                    <td class="px-5 py-3 text-stone-600">{{ $a->angkatan }}</td>
                    <td class="px-5 py-3 text-stone-600">{{ $a->semester }}</td>
                    <td class="px-5 py-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                     {{ $a->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' }}">
                            {{ $a->status_label }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-3 text-xs font-semibold">
                            <a href="{{ route('admin.anggota.edit', $a) }}" class="text-maroon hover:underline">Edit</a>

                            <form method="POST" action="{{ route('admin.anggota.reset-password', $a) }}"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Reset password ' . $a->nama . ' ke NIM?') }})">
                                @csrf
                                <button type="submit" class="text-amber-700 hover:underline">Reset PW</button>
                            </form>

                            <form method="POST" action="{{ route('admin.anggota.destroy', $a) }}"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus ' . $a->nama . '? Data absensinya ikut dihapus.') }})">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-stone-400">
                        Tidak ada anggota ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 px-5 py-3">
    <p class="text-xs text-stone-500">
        Menampilkan {{ $anggota->firstItem() ?? 0 }}–{{ $anggota->lastItem() ?? 0 }} dari {{ $anggota->total() }} anggota
    </p>
    @if ($anggota->hasPages())
        <div>{{ $anggota->links() }}</div>
    @endif
</div>
