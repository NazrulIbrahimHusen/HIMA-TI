@php
    $hariIni = now()->startOfDay();

    if ($k->status === 'closed' || $k->tanggal_selesai->lt($hariIni)) {
        $fase = ['Selesai', 'bg-stone-200 text-stone-600'];
    } elseif ($k->tanggal_mulai->lte($hariIni)) {
        $fase = ['Berlangsung', 'bg-emerald-100 text-emerald-800'];
    } else {
        $fase = ['Akan datang', 'bg-sky-100 text-sky-800'];
    }

    $mulai   = $k->tanggal_mulai->copy()->locale('id');
    $selesai = $k->tanggal_selesai->copy()->locale('id');
    $adaSertifikat = $k->sertifikat_aktif && filled($k->sertifikat_url);
@endphp

<a href="{{ route('publik.kegiatan.show', $k) }}"
   class="group flex flex-col rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $fase[1] }}">{{ $fase[0] }}</span>
        <span class="rounded-full bg-maroon-soft px-2.5 py-1 text-xs font-semibold capitalize text-maroon">{{ $k->tipe }}</span>
        @if ($adaSertifikat)
            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Sertifikat</span>
        @endif
    </div>

    <h3 class="font-serif text-lg font-bold leading-snug text-stone-800 group-hover:text-maroon">{{ $k->nama }}</h3>

    <p class="mt-3 text-sm text-stone-500">
        {{ $mulai->translatedFormat('d M Y') }}@if (!$mulai->isSameDay($selesai)) – {{ $selesai->translatedFormat('d M Y') }}@endif
    </p>
    @if ($k->lokasi)
        <p class="text-sm text-stone-500">{{ $k->lokasi }}</p>
    @endif

    <span class="mt-4 text-xs font-semibold text-maroon">Lihat detail &rarr;</span>
</a>
