@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-serif text-2xl font-bold text-stone-800">Pengumuman</h2>
            <p class="text-sm text-stone-500">Pengumuman yang diterbitkan tampil di beranda dan halaman Pengumuman.</p>
        </div>
        <a href="{{ route('admin.pengumuman.create') }}" class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-dark">+ Buat Pengumuman</a>
    </div>

    <form id="filterForm" method="GET" action="{{ route('admin.pengumuman.index') }}"
          class="mb-4 grid grid-cols-1 gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-4">
        <div class="sm:col-span-2">
            <label for="q" class="mb-1 block text-xs font-medium text-stone-500">Cari judul</label>
            <input id="q" type="search" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Ketik untuk mencari…"
                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Status</label>
            <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Semua</option>
                <option value="terbit" @selected(request('status') === 'terbit')>Terbit</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            </select>
        </div>
        <div class="flex items-end">
            <a href="{{ route('admin.pengumuman.index') }}" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-center text-sm text-stone-600 hover:bg-stone-50">Reset</a>
        </div>
    </form>

    <div id="tableWrap" class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        @include('admin.pengumuman._tabel')
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
