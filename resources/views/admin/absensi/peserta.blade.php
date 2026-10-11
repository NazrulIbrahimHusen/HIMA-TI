@extends('layouts.admin')

@section('title', 'Ubah Absensi')

@section('content')
    @php
        $label = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];
        $warna = [
            'hadir' => 'bg-emerald-100 text-emerald-800',
            'izin'  => 'bg-sky-100 text-sky-800',
            'sakit' => 'bg-amber-100 text-amber-800',
            'alpa'  => 'bg-red-100 text-red-800',
        ];
    @endphp

    <div class="mx-auto max-w-3xl space-y-5">
        <a href="{{ route('admin.absensi.kegiatan', $kegiatan) }}" class="inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke rekap</a>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="mb-5">
                <p class="text-xs uppercase tracking-wide text-stone-400">{{ $kegiatan->nama }}</p>
                <h2 class="font-serif text-xl font-bold text-stone-800">{{ $nama }}</h2>
                <p class="mt-1 text-sm text-stone-500">
                    <span class="font-mono">{{ $nim }}</span> ·
                    {{ $tipe === 'anggota' ? 'Anggota' : 'Tamu' }}
                </p>
            </div>

            <form method="POST" action="{{ route('admin.absensi.peserta.update', [$kegiatan, $nim]) }}">
                @csrf
                @method('PUT')

                <div class="space-y-3">
                    @foreach ($sesi as $s)
                        @php $row = $aktif->get($s->id); @endphp
                        <div class="grid grid-cols-1 items-center gap-3 rounded-xl border border-stone-200 bg-stone-50 p-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <p class="font-medium">{{ $s->nama_sesi }}</p>
                                <p class="text-xs text-stone-500">
                                    {{ $s->waktu_mulai->locale('id')->translatedFormat('d M, H:i') }} – {{ $s->waktu_selesai->format('H:i') }}
                                    @if ($row)
                                        · saat ini
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $warna[$row->status] }}">{{ $label[$row->status] }}</span>
                                        @if ($row->waktu_absen) · {{ $row->waktu_absen->format('d M H:i') }} ({{ strtoupper($row->metode) }}) @endif
                                    @else
                                        · belum ada data
                                    @endif
                                </p>
                            </div>
                            <select name="status[{{ $s->id }}]"
                                    class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                                <option value="">{{ $row ? '— tidak diubah —' : '— tidak dicatat —' }}</option>
                                @foreach ($label as $v => $l)
                                    @continue($row && $row->status === $v)
                                    <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <label class="mb-1 block text-xs font-medium text-stone-500">Keterangan / alasan perubahan <span class="text-stone-400">(opsional, tercatat di riwayat)</span></label>
                    <input type="text" name="keterangan" maxlength="500" placeholder="mis. izin sakit, surat dokter menyusul"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" class="rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white hover:bg-maroon-dark">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        {{-- Riwayat perubahan --}}
        <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-200 px-5 py-4">
                <h3 class="font-serif text-lg font-bold text-stone-800">Riwayat Perubahan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
                        <tr>
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3">Sesi</th>
                            <th class="px-5 py-3">Perubahan</th>
                            <th class="px-5 py-3">Oleh</th>
                            <th class="px-5 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($log as $l)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 text-stone-500">{{ \Illuminate\Support\Carbon::parse($l->created_at)->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-3">{{ $sesiPerRow[$l->absensi_id]->sesi?->nama_sesi ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $warna[$l->status_lama] }}">{{ $label[$l->status_lama] }}</span>
                                    &rarr;
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $warna[$l->status_baru] }}">{{ $label[$l->status_baru] }}</span>
                                </td>
                                <td class="px-5 py-3 text-stone-600">{{ $l->changedBy?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-stone-500">{{ $l->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-stone-400">Belum ada perubahan status.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Hapus --}}
        <form method="POST" action="{{ route('admin.absensi.peserta.hapus', [$kegiatan, $nim]) }}"
              onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus semua data absensi ' . $nama . ' pada kegiatan ini?') }})"
              class="text-right">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-semibold text-red-700 hover:underline">Hapus semua data absensi peserta ini</button>
        </form>
    </div>
@endsection
