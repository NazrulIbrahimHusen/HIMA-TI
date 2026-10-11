@extends('layouts.admin')

@section('title', 'Absensi Kegiatan')

@section('content')
    <a href="{{ route('admin.absensi.index') }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke daftar kegiatan</a>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h2 class="font-serif text-2xl font-bold text-stone-800">{{ $kegiatan->nama }}</h2>
            <p class="text-sm text-stone-500">
                {{ $kegiatan->tanggal_mulai->locale('id')->translatedFormat('d F Y') }}
                · {{ $sesi->count() }} sesi
                · <a href="{{ route('admin.kegiatan.show', $kegiatan) }}" class="text-maroon hover:underline">Detail kegiatan</a>
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.absensi.export.excel', $kegiatan) }}"
               class="rounded-lg border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Export Excel</a>
            <a href="{{ route('admin.absensi.export.pdf', $kegiatan) }}"
               class="rounded-lg border border-red-700 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Export PDF</a>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
        @php
            $kartu = [
                ['Total Peserta', $ringkasan['total'],       'Anggota & tamu'],
                ['Hadir',         $ringkasan['hadir'],       $sesi->count() > 1 ? 'Hadir di semua sesi' : 'Status hadir'],
                ['Tidak Hadir',   $ringkasan['tidak_hadir'], 'Izin, sakit, alpa, atau belum lengkap'],
                ['Tamu',          $ringkasan['tamu'],        'Bukan anggota HIMA'],
            ];
        @endphp
        @foreach ($kartu as [$label, $nilai, $hint])
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-stone-500">{{ $label }}</p>
                <p class="mt-2 font-serif text-3xl font-bold text-maroon">{{ $nilai }}</p>
                <p class="mt-1 text-xs text-stone-400">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    {{-- Catat manual --}}
    <div class="mb-6 rounded-2xl border border-stone-200 bg-white shadow-sm" x-data="{ buka: {{ $errors->any() ? 'true' : 'false' }} }">
        <button type="button" @click="buka = !buka" class="flex w-full items-center justify-between px-5 py-4 text-left">
            <span class="font-serif text-lg font-bold text-stone-800">Catat Absensi Manual</span>
            <span class="text-sm font-semibold text-maroon" x-text="buka ? 'Tutup' : 'Buka'"></span>
        </button>

        <div x-show="buka" x-cloak class="border-t border-stone-200 p-5">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.absensi.tambah', $kegiatan) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-stone-500">NIM</label>
                    <input type="text" name="nim" value="{{ old('nim') }}" required maxlength="10" inputmode="numeric" pattern="[0-9]*"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-stone-500">Nama <span class="text-stone-400">(wajib jika bukan anggota)</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" maxlength="255"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-stone-500">Sesi</label>
                    <select name="sesi_id" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                        @if ($sesi->count() > 1)
                            <option value="semua">Semua sesi</option>
                        @endif
                        @foreach ($sesi as $s)
                            <option value="{{ $s->id }}" @selected(old('sesi_id') == $s->id)>{{ $s->nama_sesi }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-stone-500">Status</label>
                    <select name="status" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                        @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('status', 'hadir') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="mb-1 block text-xs font-medium text-stone-500">Keterangan <span class="text-stone-400">(opsional, mis. alasan izin)</span></label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}" maxlength="500"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-dark">Simpan</button>
                </div>
            </form>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 pt-4">
                <p class="max-w-xl text-xs text-stone-500">
                    Setelah sesi berakhir, klik tombol ini agar anggota aktif yang belum absen tercatat <strong>Alpa</strong>.
                    Data ini dibutuhkan agar monitoring keaktifan dapat menghitung ketidakhadiran.
                    @if ($sesiSelesai === 0) <span class="text-amber-700">Belum ada sesi yang berakhir.</span> @endif
                </p>
                <form method="POST" action="{{ route('admin.absensi.isi-alpa', $kegiatan) }}"
                      onsubmit="return confirm('Catat Alpa untuk semua anggota aktif yang belum absen pada sesi yang sudah berakhir?')">
                    @csrf
                    <button type="submit" class="rounded-lg border border-amber-700 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50">
                        Isi Alpa Otomatis
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Filter (search real-time) --}}
    <form id="filterForm" method="GET" action="{{ route('admin.absensi.kegiatan', $kegiatan) }}"
          class="mb-4 grid grid-cols-1 gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-4">
        <div class="sm:col-span-2">
            <label for="q" class="mb-1 block text-xs font-medium text-stone-500">Cari (nama / NIM)</label>
            <input id="q" type="search" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Ketik untuk mencari…"
                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Peserta</label>
            <select name="tipe" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Semua</option>
                <option value="anggota" @selected(request('tipe') === 'anggota')>Anggota</option>
                <option value="tamu" @selected(request('tipe') === 'tamu')>Tamu</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-stone-800 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">Terapkan</button>
            <a href="{{ route('admin.absensi.kegiatan', $kegiatan) }}" class="rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-600 hover:bg-stone-50">Reset</a>
        </div>
    </form>

    <div id="tableWrap" class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        @include('admin.absensi._rekap')
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
