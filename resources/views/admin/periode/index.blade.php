@extends('layouts.admin')

@section('title', 'Periode Kepengurusan')

@section('content')
    <a href="{{ route('admin.pengurus.index') }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke struktur pengurus</a>

    <div class="mb-6">
        <h2 class="font-serif text-2xl font-bold text-stone-800">Periode Kepengurusan</h2>
        <p class="text-sm text-stone-500">
            Saat terjadi pergantian pengurus, buat periode baru lalu aktifkan. Periode lama otomatis diarsipkan dan datanya tetap tersimpan.
        </p>
    </div>

    <div class="mb-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 font-serif text-lg font-bold text-stone-800">Tambah Periode</h3>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.periode.store') }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-4">
            @csrf
            <div>
                <label for="tahun_mulai" class="mb-1 block text-xs font-medium text-stone-500">Tahun mulai</label>
                <input id="tahun_mulai" type="number" name="tahun_mulai" min="2000" max="2100" required
                       value="{{ old('tahun_mulai', date('Y')) }}"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
            </div>
            <div>
                <label for="tahun_selesai" class="mb-1 block text-xs font-medium text-stone-500">Tahun selesai</label>
                <input id="tahun_selesai" type="number" name="tahun_selesai" min="2000" max="2100" required
                       value="{{ old('tahun_selesai', date('Y') + 1) }}"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
            </div>
            <label class="flex items-center gap-2 pb-2 text-sm text-stone-600">
                <input type="checkbox" name="aktifkan" value="1" @checked(old('aktifkan')) class="h-4 w-4 rounded border-stone-300">
                Jadikan periode aktif
            </label>
            <button type="submit" class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-dark">Tambah</button>
        </form>
    </div>

    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
                    <tr>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Divisi</th>
                        <th class="px-5 py-3">Pengurus</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($periode as $p)
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.pengurus.index', ['periode' => $p->id]) }}" class="font-serif text-base font-bold text-stone-800 hover:text-maroon">{{ $p->nama }}</a>
                            </td>
                            <td class="px-5 py-3 text-stone-600">{{ $p->divisi_count }}</td>
                            <td class="px-5 py-3 text-stone-600">{{ $p->pengurus_count }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $p->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' }}">
                                    {{ $p->status === 'aktif' ? 'Aktif' : 'Arsip' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3 text-xs font-semibold">
                                    <a href="{{ route('admin.pengurus.index', ['periode' => $p->id]) }}" class="text-maroon hover:underline">Lihat struktur</a>

                                    @if ($p->status !== 'aktif')
                                        <form method="POST" action="{{ route('admin.periode.aktifkan', $p) }}"
                                              onsubmit="return confirm({{ \Illuminate\Support\Js::from('Aktifkan periode ' . $p->nama . '? Periode lain akan diarsipkan.') }})">
                                            @csrf
                                            <button type="submit" class="text-emerald-700 hover:underline">Aktifkan</button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.periode.edit', $p) }}" class="text-stone-600 hover:underline">Edit</a>

                                    <form method="POST" action="{{ route('admin.periode.destroy', $p) }}"
                                          onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus periode ' . $p->nama . '?') }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-700 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-stone-400">Belum ada periode. Tambahkan periode pertama di atas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
