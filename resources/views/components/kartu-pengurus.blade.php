@props(['p', 'besar' => false, 'lebar' => 'w-44'])

@php $a = $p->anggota; @endphp

<div {{ $attributes->class(['flex flex-col items-center rounded-2xl border border-stone-200 bg-white p-4 text-center shadow-sm', $lebar]) }}>
    @if ($a?->foto)
        <img src="{{ asset('storage/' . $a->foto) }}" alt="" class="{{ $besar ? 'h-20 w-20' : 'h-14 w-14' }} rounded-full object-cover">
    @else
        <div class="{{ $besar ? 'h-20 w-20 text-2xl' : 'h-14 w-14 text-lg' }} flex items-center justify-center rounded-full bg-maroon-soft font-serif font-bold text-maroon">
            {{ strtoupper(mb_substr($a?->nama ?? '?', 0, 1)) }}
        </div>
    @endif
    <p class="mt-3 text-sm font-semibold leading-snug text-stone-800">{{ $a?->nama ?? '—' }}</p>
    <span class="mt-1.5 rounded-full bg-maroon-soft px-2.5 py-0.5 text-[11px] font-semibold text-maroon">{{ $p->jabatan }}</span>
</div>
