@extends('layouts.admin')

@section('title', 'Monitoring Keaktifan')

@section('content')
    <div class="mb-6">
        <h2 class="font-serif text-2xl font-bold text-stone-800">Monitoring Keaktifan</h2>
        <p class="text-sm text-stone-500">
            Persentase kehadiran tiap anggota pada <strong>{{ $label }}</strong>.
            Anggota dihitung hadir pada sebuah kegiatan jika hadir di semua sesinya.
        </p>
    </div>

    {{-- Ringkasan --}}
    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-stone-500">Rata-rata Keaktifan</p>
            <p class="mt-2 font-serif text-3xl font-bold text-maroon">{{ is_null($ringkasan['rata']) ? '-' : $ringkasan['rata'] . '%' }}</p>
            <p class="mt-1 text-xs text-stone-400">{{ $ringkasan['dinilai'] }} dari {{ $ringkasan['jumlah'] }} anggota sudah memiliki data</p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm lg:col-span-2">
            <p class="mb-3 text-sm font-medium text-stone-500">Distribusi Grade <span class="text-stone-400">(klik untuk memfilter)</span></p>
            <div class="flex flex-wrap gap-2">
                @foreach ($grade as $huruf => $info)
                    @php $aktif = request('grade') === $huruf; @endphp
                    <a href="{{ request()->fullUrlWithQuery(['grade' => $aktif ? null : $huruf, 'page' => null]) }}"
                       title="{{ $info['label'] }} · {{ $info['rentang'] }}"
                       class="flex items-center gap-2 rounded-xl border px-3 py-2 text-sm transition {{ $aktif ? 'border-maroon bg-maroon-soft' : 'border-stone-200 hover:bg-stone-50' }}">
                        <x-grade :nilai="$huruf" />
                        <span class="font-semibold text-stone-700">{{ $ringkasan['distribusi'][$huruf] }}</span>
                    </a>
                @endforeach
                @php $aktifBelum = request('grade') === '-'; @endphp
                <a href="{{ request()->fullUrlWithQuery(['grade' => $aktifBelum ? null : '-', 'page' => null]) }}"
                   class="flex items-center gap-2 rounded-xl border px-3 py-2 text-sm transition {{ $aktifBelum ? 'border-maroon bg-maroon-soft' : 'border-stone-200 hover:bg-stone-50' }}">
                    <span class="text-xs text-stone-500">Belum ada data</span>
                    <span class="font-semibold text-stone-700">{{ $ringkasan['belum'] }}</span>
                </a>
            </div>
            <p class="mt-3 text-xs text-stone-400">
                @foreach ($grade as $huruf => $info) {{ $huruf }} = {{ $info['rentang'] }}@if (!$loop->last) · @endif @endforeach
            </p>
        </div>
    </div>

    {{-- Filter --}}
    <form id="filterForm" method="GET" action="{{ route('admin.monitoring.index') }}"
          class="mb-4 grid grid-cols-1 gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6">
        <input type="hidden" name="grade" value="{{ request('grade') }}">

        <div class="lg:col-span-2">
            <label for="q" class="mb-1 block text-xs font-medium text-stone-500">Cari (nama / NIM / prodi)</label>
            <input id="q" type="search" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Ketik untuk mencari…"
                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Rentang waktu</label>
            <select name="rentang" onchange="this.form.submit()" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                @foreach ($opsiRentang as $k => $teks)
                    <option value="{{ $k }}" @selected((string) $kode === (string) $k)>{{ $teks }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Prodi</label>
            <select name="prodi" onchange="this.form.submit()" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Semua</option>
                @foreach ($prodiList as $p)
                    <option value="{{ $p }}" @selected(request('prodi') === $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Angkatan</label>
            <select name="angkatan" onchange="this.form.submit()" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Semua</option>
                @foreach ($angkatanList as $a)
                    <option value="{{ $a }}" @selected((string) request('angkatan') === (string) $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Urutkan</label>
            <select name="urut" onchange="this.form.submit()" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="persen_desc" @selected(request('urut', 'persen_desc') === 'persen_desc')>Paling aktif</option>
                <option value="persen_asc" @selected(request('urut') === 'persen_asc')>Paling tidak aktif</option>
                <option value="nama" @selected(request('urut') === 'nama')>Nama (A–Z)</option>
            </select>
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-6 lg:justify-end">
            <span class="mr-auto text-xs text-stone-400">Status anggota:</span>
            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="aktif" @selected(request('status', 'aktif') === 'aktif')>Aktif</option>
                <option value="tidak_aktif" @selected(request('status') === 'tidak_aktif')>Tidak aktif</option>
                <option value="semua" @selected(request('status') === 'semua')>Semua</option>
            </select>
            <a href="{{ route('admin.monitoring.index') }}" class="rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-600 hover:bg-stone-50">Reset</a>
        </div>
    </form>

    <div id="tableWrap" class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        @include('admin.monitoring._tabel')
    </div>

    <p class="mt-3 text-xs text-stone-400">
        Catatan: anggota yang tidak datang baru tercatat "tidak hadir" setelah menekan <strong>Isi Alpa Otomatis</strong> di halaman Absensi kegiatan tersebut.
    </p>
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
