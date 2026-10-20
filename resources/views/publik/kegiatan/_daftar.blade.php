@if ($kegiatan->isEmpty())
    <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-12 text-center text-sm text-stone-400">Tidak ada kegiatan yang cocok.</p>
@else
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($kegiatan as $k)
            @include('publik.kegiatan._kartu', ['k' => $k])
        @endforeach
    </div>

    @if ($kegiatan->hasPages())
        <div class="mt-6">{{ $kegiatan->links() }}</div>
    @endif
@endif
