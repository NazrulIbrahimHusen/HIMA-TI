@extends('layouts.publik')

@section('title', 'Kegiatan')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">
        <div class="mb-6">
            <h1 class="font-serif text-3xl font-bold text-stone-800">Kegiatan</h1>
            <p class="text-sm text-stone-500">Seluruh kegiatan HIMA Informatika, dari yang akan datang hingga yang sudah selesai.</p>
        </div>

        <form id="filterForm" method="GET" action="{{ route('publik.kegiatan.index') }}"
              class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="q" class="mb-1 block text-xs font-medium text-stone-500">Cari kegiatan</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Nama atau lokasi…"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-stone-500">Tampilkan</label>
                <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                    <option value="">Semua kegiatan</option>
                    <option value="mendatang" @selected(request('status') === 'mendatang')>Akan datang & berlangsung</option>
                    <option value="selesai" @selected(request('status') === 'selesai')>Sudah selesai</option>
                </select>
            </div>
            <div class="flex items-end">
                <a href="{{ route('publik.kegiatan.index') }}" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-center text-sm text-stone-600 hover:bg-stone-50">Reset</a>
            </div>
        </form>

        <div id="tableWrap">
            @include('publik.kegiatan._daftar')
        </div>
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
