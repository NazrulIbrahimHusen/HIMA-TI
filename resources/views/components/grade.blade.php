@props(['nilai' => null])

@php
    $warna = [
        'A' => 'bg-emerald-100 text-emerald-800',
        'B' => 'bg-lime-100 text-lime-800',
        'C' => 'bg-amber-100 text-amber-800',
        'D' => 'bg-orange-100 text-orange-800',
        'E' => 'bg-red-100 text-red-800',
    ];
@endphp

@if ($nilai)
    <span {{ $attributes->class(['inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold', $warna[$nilai] ?? 'bg-stone-200 text-stone-600']) }}>{{ $nilai }}</span>
@else
    <span {{ $attributes->class(['text-xs text-stone-400']) }}>belum ada data</span>
@endif
