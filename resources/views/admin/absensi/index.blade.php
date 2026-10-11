@extends('layouts.admin')

@section('title', 'Absensi')

@section('content')
    <div class="mb-6">
        <h2 class="font-serif text-2xl font-bold text-stone-800">Absensi Kegiatan</h2>
        <p class="text-sm text-stone-500">Pilih kegiatan untuk melihat rekap, mencatat manual, mengubah status, dan mengekspor.</p>
    </div>

    <form id="filterForm" method="GET" action="{{ route('admin.absensi.index') }}"
          class="mb-4 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
        <label for="q" class="mb-1 block text-xs font-medium text-stone-500">Cari kegiatan (nama / lokasi)</label>
        <input id="q" type="search" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Ketik untuk mencari…"
               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon sm:max-w-md">
    </form>

    <div id="tableWrap" class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        @include('admin.absensi._daftar')
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('filterForm');
            const wrap = document.getElementById('tableWrap');
            const input = document.getElementById('q');
            let timer = null;

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const params = new URLSearchParams(new FormData(form));
                    for (const k of [...params.keys()]) { if (!params.get(k)) params.delete(k); }
                    const url = form.action + (params.toString() ? '?' + params : '');
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.text())
                        .then(html => { wrap.innerHTML = html; history.replaceState(null, '', url); });
                }, 300);
            });
        })();
    </script>
@endpush
