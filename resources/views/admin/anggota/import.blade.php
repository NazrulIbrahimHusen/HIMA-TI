@extends('layouts.admin')

@section('title', 'Import Anggota')

@section('content')
    @php $hasil = session('import_result'); @endphp

    <div class="mx-auto max-w-3xl space-y-5">
        <a href="{{ route('admin.anggota.index') }}" class="inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke daftar</a>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 font-serif text-xl font-bold text-stone-800">Import Anggota</h2>
            <p class="mb-5 text-sm text-stone-500">Tambahkan banyak anggota sekaligus. Setiap anggota otomatis mendapat akun portal dengan password awal = NIM.</p>

            <ol class="mb-5 list-inside list-decimal space-y-1.5 text-sm text-stone-600">
                <li>
                    Unduh template:
                    <a href="{{ route('admin.anggota.import.template') }}" class="font-semibold text-maroon hover:underline">template-import-anggota.xlsx</a>
                </li>
                <li>Isi kolom <span class="font-mono text-xs">nim, nama, prodi, angkatan, semester</span> — <strong>hapus dua baris contoh</strong> sebelum mengunggah.</li>
                <li>NIM berupa angka maksimal 10 digit. NIM yang sudah terdaftar akan dilewati.</li>
                <li>Unggah file (.xlsx, .xls, atau .csv, maksimal 5 MB).</li>
            </ol>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.anggota.import.store') }}" enctype="multipart/form-data"
                  x-data="{ memproses: false }" @submit="memproses = true">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="mb-4 block w-full text-sm text-stone-600 file:mr-4 file:rounded-lg file:border-0 file:bg-maroon-soft file:px-4 file:py-2 file:text-sm file:font-semibold file:text-maroon hover:file:bg-[#f0dce0]">

                <button type="submit" :disabled="memproses"
                        class="rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-maroon-dark disabled:cursor-wait disabled:opacity-60">
                    <span x-show="!memproses">Import Sekarang</span>
                    <span x-show="memproses" x-cloak>Memproses… jangan tutup halaman ini</span>
                </button>
            </form>
        </div>

        @if ($hasil)
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 font-serif text-lg font-bold text-stone-800">Hasil Import</h3>

                <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl bg-emerald-50 p-3 text-center">
                        <p class="font-serif text-2xl font-bold text-emerald-700">{{ $hasil['dibuat'] }}</p>
                        <p class="text-xs text-emerald-800">Ditambahkan</p>
                    </div>
                    <div class="rounded-xl bg-sky-50 p-3 text-center">
                        <p class="font-serif text-2xl font-bold text-sky-700">{{ $hasil['dipulihkan'] }}</p>
                        <p class="text-xs text-sky-800">Dipulihkan</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-3 text-center">
                        <p class="font-serif text-2xl font-bold text-amber-700">{{ $hasil['dilewati'] }}</p>
                        <p class="text-xs text-amber-800">Dilewati (sudah ada)</p>
                    </div>
                    <div class="rounded-xl bg-red-50 p-3 text-center">
                        <p class="font-serif text-2xl font-bold text-red-700">{{ $hasil['total_gagal'] }}</p>
                        <p class="text-xs text-red-800">Gagal</p>
                    </div>
                </div>

                @if ($hasil['total_gagal'] > 0)
                    <p class="mb-2 text-sm font-medium text-stone-700">Baris yang gagal diimpor:</p>
                    <div class="overflow-x-auto rounded-lg border border-stone-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-stone-50 text-xs uppercase text-stone-500">
                                <tr><th class="px-4 py-2">Baris</th><th class="px-4 py-2">NIM</th><th class="px-4 py-2">Masalah</th></tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($hasil['gagal'] as $g)
                                    <tr>
                                        <td class="px-4 py-2">{{ $g['baris'] }}</td>
                                        <td class="px-4 py-2 font-mono text-xs">{{ $g['nim'] ?: '-' }}</td>
                                        <td class="px-4 py-2 text-red-700">{{ $g['pesan'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($hasil['total_gagal'] > count($hasil['gagal']))
                        <p class="mt-2 text-xs text-stone-500">Menampilkan {{ count($hasil['gagal']) }} dari {{ $hasil['total_gagal'] }} baris gagal.</p>
                    @endif
                    <p class="mt-3 text-xs text-stone-500">Perbaiki baris di atas lalu unggah ulang. Baris yang sudah berhasil akan dilewati otomatis.</p>
                @endif
            </div>
        @endif
    </div>
@endsection
