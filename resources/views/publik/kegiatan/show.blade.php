@extends('layouts.publik')

@section('title', $kegiatan->nama)

@section('content')
    @php
        $hariIni = now()->startOfDay();
        if ($kegiatan->status === 'closed' || $kegiatan->tanggal_selesai->lt($hariIni)) {
            $fase = ['Selesai', 'bg-stone-200 text-stone-600'];
        } elseif ($kegiatan->tanggal_mulai->lte($hariIni)) {
            $fase = ['Berlangsung', 'bg-emerald-100 text-emerald-800'];
        } else {
            $fase = ['Akan datang', 'bg-sky-100 text-sky-800'];
        }
        $mulai   = $kegiatan->tanggal_mulai->copy()->locale('id');
        $selesai = $kegiatan->tanggal_selesai->copy()->locale('id');
    @endphp

    <div class="mx-auto max-w-4xl px-4 py-10">
        <a href="{{ route('publik.kegiatan.index') }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Semua kegiatan</a>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $fase[1] }}">{{ $fase[0] }}</span>
                <span class="rounded-full bg-maroon-soft px-2.5 py-1 text-xs font-semibold capitalize text-maroon">{{ $kegiatan->tipe }}</span>
            </div>

            <h1 class="font-serif text-3xl font-bold leading-tight text-stone-800">{{ $kegiatan->nama }}</h1>

            <dl class="mt-5 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-stone-400">Tanggal</dt>
                    <dd class="mt-0.5 text-stone-700">
                        {{ $mulai->translatedFormat('l, d F Y') }}@if (!$mulai->isSameDay($selesai)) – {{ $selesai->translatedFormat('l, d F Y') }}@endif
                    </dd>
                </div>
                @if ($kegiatan->lokasi)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-stone-400">Lokasi</dt>
                        <dd class="mt-0.5 text-stone-700">{{ $kegiatan->lokasi }}</dd>
                    </div>
                @endif
            </dl>

            @if ($kegiatan->deskripsi)
                <div class="mt-6 border-t border-stone-200 pt-6 text-stone-600">{!! nl2br(e($kegiatan->deskripsi)) !!}</div>
            @endif
        </div>

        {{-- Jadwal sesi --}}
        <div class="mt-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-serif text-lg font-bold text-stone-800">Jadwal</h2>
            <ul class="divide-y divide-stone-100">
                @foreach ($kegiatan->sesiAbsensi as $s)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                        <span class="font-medium text-stone-700">{{ $s->nama_sesi }}</span>
                        <span class="text-stone-500">
                            {{ $s->waktu_mulai->copy()->locale('id')->translatedFormat('d M Y, H:i') }}
                            – {{ $s->waktu_mulai->isSameDay($s->waktu_selesai) ? $s->waktu_selesai->format('H:i') : $s->waktu_selesai->copy()->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Sertifikat --}}
        <div class="mt-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 font-serif text-lg font-bold text-stone-800">Sertifikat</h2>

            @if ($statusSertifikat === 'siap')
                <p class="mb-4 text-sm text-stone-600">Kamu tercatat hadir di seluruh sesi kegiatan ini. Sertifikat siap diunduh.</p>
                <a href="{{ route('mahasiswa.sertifikat', $kegiatan) }}" class="inline-block rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white hover:bg-maroon-dark">Unduh Sertifikat</a>
            @elseif ($statusSertifikat === 'belum_login')
                <p class="mb-4 text-sm text-stone-600">{{ $pesanSertifikat }}</p>
                <a href="{{ route('mahasiswa.login') }}" class="inline-block rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white hover:bg-maroon-dark">Masuk</a>
            @else
                <p class="rounded-lg bg-stone-100 p-3 text-sm text-stone-600">{{ $pesanSertifikat }}</p>
            @endif
        </div>
    </div>
@endsection
