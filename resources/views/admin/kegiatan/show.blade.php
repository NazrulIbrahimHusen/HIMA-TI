@extends('layouts.admin')

@section('title', 'Detail Kegiatan')

@section('content')
    @php
        $badge = [
            'draft'     => ['Draft',          'bg-stone-200 text-stone-600'],
            'published' => ['Dipublikasikan', 'bg-emerald-100 text-emerald-800'],
            'closed'    => ['Selesai',        'bg-red-100 text-red-800'],
        ];
        $mulai   = $kegiatan->tanggal_mulai->locale('id');
        $selesai = $kegiatan->tanggal_selesai->locale('id');
    @endphp

    <a href="{{ route('admin.kegiatan.index') }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke daftar</a>

    {{-- Header --}}
    <div class="mb-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$kegiatan->status][1] }}">{{ $badge[$kegiatan->status][0] }}</span>
                    <span class="rounded-full bg-maroon-soft px-2.5 py-1 text-xs font-semibold capitalize text-maroon">{{ $kegiatan->tipe }}</span>
                </div>
                <h2 class="font-serif text-2xl font-bold text-stone-800">{{ $kegiatan->nama }}</h2>
                <p class="mt-1 text-sm text-stone-500">
                    {{ $mulai->translatedFormat('l, d F Y') }}
                    @if (!$mulai->isSameDay($selesai)) – {{ $selesai->translatedFormat('l, d F Y') }} @endif
                    @if ($kegiatan->lokasi) · {{ $kegiatan->lokasi }} @endif
                </p>
                @if ($kegiatan->deskripsi)
                    <p class="mt-3 max-w-2xl whitespace-pre-line text-sm text-stone-600">{{ $kegiatan->deskripsi }}</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($kegiatan->status === 'draft')
                    <form method="POST" action="{{ route('admin.kegiatan.status', $kegiatan) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="published">
                        <button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Publikasikan</button>
                    </form>
                @elseif ($kegiatan->status === 'published')
                    <form method="POST" action="{{ route('admin.kegiatan.status', $kegiatan) }}"
                          onsubmit="return confirm('Tutup kegiatan? Link & QR absensi tidak akan menerima absensi lagi.')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="closed">
                        <button class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Tutup Kegiatan</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.kegiatan.status', $kegiatan) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="published">
                        <button class="rounded-lg border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Buka Kembali</button>
                    </form>
                @endif
                <a href="{{ route('admin.kegiatan.edit', $kegiatan) }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">Edit</a>
            </div>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Link & QR --}}
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm lg:order-2">
            <h3 class="mb-3 font-serif text-lg font-bold text-stone-800">Link & QR Absensi</h3>

            @if ($kegiatan->status === 'published')
                <div class="mb-4 flex justify-center qr-box rounded-xl border border-stone-200 bg-white p-3 [&>svg]:h-auto [&>svg]:max-w-full">{!! $qrSvg !!}</div>

                <div x-data="{ ok: false }" class="mb-3">
                    <p class="mb-1 text-xs font-medium text-stone-500">Link absensi</p>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ $linkAbsen }}" onclick="this.select()"
                               class="min-w-0 flex-1 rounded-lg border border-stone-300 bg-stone-50 px-3 py-2 font-mono text-xs">
                        <button type="button"
                                @click="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($linkAbsen) }}); ok = true; setTimeout(() => ok = false, 2000)"
                                class="rounded-lg bg-maroon px-3 py-2 text-xs font-semibold text-white hover:bg-maroon-dark">
                            <span x-show="!ok">Salin</span><span x-show="ok" x-cloak>Tersalin</span>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 text-xs font-semibold">
                    <a href="{{ route('admin.kegiatan.qr', $kegiatan) }}" target="_blank" class="text-maroon hover:underline">Tampilkan layar penuh</a>
                    <form method="POST" action="{{ route('admin.kegiatan.token', $kegiatan) }}"
                          onsubmit="return confirm('Buat token baru? Link dan QR yang sudah dibagikan akan tidak berlaku.')">
                        @csrf
                        <button class="text-amber-700 hover:underline">Ganti token</button>
                    </form>
                </div>
            @elseif ($kegiatan->status === 'draft')
                <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800">Kegiatan masih draft. Publikasikan agar link & QR absensi tersedia.</p>
            @else
                <p class="rounded-lg bg-stone-100 p-3 text-sm text-stone-600">Kegiatan sudah ditutup. Link & QR tidak menerima absensi. Klik "Buka Kembali" jika perlu membukanya lagi.</p>
            @endif
        </div>

        {{-- Sesi --}}
        <div class="rounded-2xl border border-stone-200 bg-white shadow-sm lg:order-1 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4">
                <h3 class="font-serif text-lg font-bold text-stone-800">Sesi Absensi</h3>
                <div class="flex items-center gap-4">
                    <p class="text-sm text-stone-500">Total hadir: <strong class="text-maroon">{{ $totalHadir }}</strong></p>
                    <a href="{{ route('admin.absensi.kegiatan', $kegiatan) }}"
                       class="rounded-lg bg-maroon px-3 py-1.5 text-xs font-semibold text-white hover:bg-maroon-dark">Kelola Absensi</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
                        <tr>
                            <th class="px-5 py-3">Sesi</th>
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3">Kondisi</th>
                            <th class="px-5 py-3 text-right">Hadir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($kegiatan->sesiAbsensi as $s)
                            <tr>
                                <td class="px-5 py-3 font-medium">{{ $s->nama_sesi }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-stone-600">
                                    {{ $s->waktu_mulai->locale('id')->translatedFormat('d M, H:i') }}
                                    – {{ $s->waktu_mulai->isSameDay($s->waktu_selesai) ? $s->waktu_selesai->format('H:i') : $s->waktu_selesai->locale('id')->translatedFormat('d M, H:i') }}
                                </td>
                                <td class="px-5 py-3">
                                    @if ($s->isAktif())
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Berlangsung</span>
                                    @elseif ($s->isBelumMulai())
                                        <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">Belum dimulai</span>
                                    @else
                                        <span class="rounded-full bg-stone-200 px-2.5 py-1 text-xs font-semibold text-stone-600">Selesai</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-xs">{{ $s->hadir_count }} / {{ $s->absensi_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Absensi terbaru --}}
    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-200 px-5 py-4">
            <h3 class="font-serif text-lg font-bold text-stone-800">Absensi Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
                    <tr>
                        <th class="px-5 py-3">NIM</th>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Peserta</th>
                        <th class="px-5 py-3">Sesi</th>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($absensiTerbaru as $a)
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-3 font-mono text-xs">{{ $a->nim }}</td>
                            <td class="px-5 py-3 font-medium">{{ $a->nama }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $a->tipe_peserta === 'anggota' ? 'bg-maroon-soft text-maroon' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $a->tipe_peserta === 'anggota' ? 'Anggota' : 'Tamu' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-stone-600">{{ $a->sesi?->nama_sesi }}</td>
                            <td class="px-5 py-3 uppercase text-stone-500">{{ $a->metode }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ optional($a->waktu_absen ?? $a->created_at)->format('d M, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-stone-400">Belum ada yang absen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
