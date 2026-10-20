@extends('layouts.publik')

@section('title', 'Pengumuman')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10">
        <div class="mb-6">
            <h1 class="font-serif text-3xl font-bold text-stone-800">Pengumuman</h1>
            <p class="text-sm text-stone-500">Informasi terbaru dari HIMA Informatika.</p>
        </div>

        <div class="space-y-3">
            @forelse ($pengumuman as $p)
                <a href="{{ route('publik.pengumuman.show', $p) }}" class="block rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <p class="text-xs text-stone-400">{{ $p->published_at->locale('id')->translatedFormat('l, d F Y') }}</p>
                    <h2 class="mt-1 font-serif text-xl font-bold text-stone-800">{{ $p->judul }}</h2>
                    <p class="mt-1 text-sm text-stone-500">{{ \Illuminate\Support\Str::limit(strip_tags($p->konten), 200) }}</p>
                </a>
            @empty
                <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-12 text-center text-sm text-stone-400">Belum ada pengumuman.</p>
            @endforelse
        </div>

        @if ($pengumuman->hasPages())
            <div class="mt-6">{{ $pengumuman->links() }}</div>
        @endif
    </div>
@endsection
