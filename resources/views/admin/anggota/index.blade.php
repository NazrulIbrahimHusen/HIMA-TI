@extends('layouts.admin')

@section('title', 'Anggota')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-serif text-2xl font-bold text-stone-800">Data Anggota</h2>
            <p class="text-sm text-stone-500">Kelola anggota HIMA beserta akun portal mahasiswa.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.anggota.import') }}"
               class="rounded-lg border border-maroon px-4 py-2 text-sm font-semibold text-maroon transition hover:bg-maroon-soft">
                Import CSV/Excel
            </a>
            <a href="{{ route('admin.anggota.create') }}"
               class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white transition hover:bg-maroon-dark">
                + Tambah Anggota
            </a>
        </div>
    </div>

    {{-- Search real-time + filter (submit) --}}
    <form id="filterForm" method="GET" action="{{ route('admin.anggota.index') }}"
          class="mb-4 grid grid-cols-1 gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6">
        <div class="lg:col-span-2">
            <label for="q" class="mb-1 block text-xs font-medium text-stone-500">Cari (nama / NIM / prodi)</label>
            <input id="q" type="search" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Ketik untuk mencari…"
                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Prodi</label>
            <select name="prodi" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Semua</option>
                @foreach ($prodiList as $p)
                    <option value="{{ $p }}" @selected(request('prodi') === $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Angkatan</label>
            <select name="angkatan" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Semua</option>
                @foreach ($angkatanList as $a)
                    <option value="{{ $a }}" @selected((string) request('angkatan') === (string) $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Status</label>
            <select name="status" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Semua</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="tidak_aktif" @selected(request('status') === 'tidak_aktif')>Tidak aktif</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-stone-800 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">Terapkan</button>
            <a href="{{ route('admin.anggota.index') }}" class="rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-600 hover:bg-stone-50">Reset</a>
        </div>
    </form>

    <div id="tableWrap" class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        @include('admin.anggota._table')
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
