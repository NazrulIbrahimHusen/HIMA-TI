@extends('layouts.admin')

@section('title', 'Kegiatan')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-serif text-2xl font-bold text-stone-800">Data Kegiatan</h2>
            <p class="text-sm text-stone-500">Kelola kegiatan HIMA, sesi absensi, serta link dan QR absensi.</p>
        </div>
        <a href="{{ route('admin.kegiatan.create') }}"
           class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white transition hover:bg-maroon-dark">
            + Buat Kegiatan
        </a>
    </div>

    <form id="filterForm" method="GET" action="{{ route('admin.kegiatan.index') }}"
          class="mb-4 grid grid-cols-1 gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label for="q" class="mb-1 block text-xs font-medium text-stone-500">Cari (nama / lokasi)</label>
            <input id="q" type="search" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Ketik untuk mencari…"
                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Status</label>
            <select name="status" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Semua</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="published" @selected(request('status') === 'published')>Dipublikasikan</option>
                <option value="closed" @selected(request('status') === 'closed')>Selesai</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Tipe</label>
            <select name="tipe" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Semua</option>
                <option value="internal" @selected(request('tipe') === 'internal')>Internal</option>
                <option value="kolaborasi" @selected(request('tipe') === 'kolaborasi')>Kolaborasi</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-stone-800 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">Terapkan</button>
            <a href="{{ route('admin.kegiatan.index') }}" class="rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-600 hover:bg-stone-50">Reset</a>
        </div>
    </form>

    <div id="tableWrap" class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        @include('admin.kegiatan._table')
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('filterForm');
            const wrap = document.getElementById('tableWrap');
            const input = document.getElementById('q');
            let timer = null;

            function muat() {
                const params = new URLSearchParams(new FormData(form));
                for (const k of [...params.keys()]) {
                    if (!params.get(k)) params.delete(k);
                }
                const url = form.action + (params.toString() ? '?' + params : '');

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.text())
                    .then(html => {
                        wrap.innerHTML = html;
                        history.replaceState(null, '', url);
                    });
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(muat, 300);
            });
        })();
    </script>
@endpush
