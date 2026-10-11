@extends('layouts.admin')

@section('title', 'Edit Anggota')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('admin.anggota.index') }}" class="mb-4 inline-block text-sm text-stone-500 hover:text-maroon">&larr; Kembali ke daftar</a>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 font-serif text-xl font-bold text-stone-800">Edit Anggota</h2>

            <form method="POST" action="{{ route('admin.anggota.update', $anggota) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.anggota._form')

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('admin.anggota.index') }}" class="rounded-lg border border-stone-300 px-5 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">Batal</a>
                    <button type="submit" class="rounded-lg bg-maroon px-5 py-2.5 text-sm font-semibold text-white hover:bg-maroon-dark">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
