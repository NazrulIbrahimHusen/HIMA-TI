@extends('layouts.publik')

@section('title', $pengumuman->judul)

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10">
        <a href="{{ route('publik.pengumuman.index') }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Semua pengumuman</a>

        <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs text-stone-400">{{ $pengumuman->published_at->locale('id')->translatedFormat('l, d F Y · H:i') }} WIB</p>
            <h1 class="mt-2 font-serif text-3xl font-bold leading-tight text-stone-800">{{ $pengumuman->judul }}</h1>
            <div class="mt-6 leading-relaxed text-stone-600">{!! nl2br(e($pengumuman->konten)) !!}</div>
        </article>

        @if ($lainnya->isNotEmpty())
            <h2 class="mb-3 mt-10 font-serif text-lg font-bold text-stone-800">Pengumuman lainnya</h2>
            <ul class="space-y-2">
                @foreach ($lainnya as $p)
                    <li>
                        <a href="{{ route('publik.pengumuman.show', $p) }}" class="flex items-baseline justify-between gap-4 rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm hover:bg-stone-50">
                            <span class="font-medium text-stone-700">{{ $p->judul }}</span>
                            <span class="shrink-0 text-xs text-stone-400">{{ $p->published_at->locale('id')->translatedFormat('d M Y') }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
