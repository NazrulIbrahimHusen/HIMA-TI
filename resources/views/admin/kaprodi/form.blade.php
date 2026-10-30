@extends('layouts.admin')

@section('title', $kaprodi ? 'Edit Kaprodi' : 'Tambah Kaprodi')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.super.kaprodi') }}"
           class="text-sm text-maroon hover:underline">&larr; Kembali ke Daftar Kaprodi</a>
        <h2 class="mt-2 font-serif text-2xl font-bold text-stone-800">
            {{ $kaprodi ? 'Edit Akun Kaprodi' : 'Tambah Akun Kaprodi' }}
        </h2>
    </div>

    <div class="max-w-lg rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $kaprodi ? route('admin.super.kaprodi.update', $kaprodi) : route('admin.super.kaprodi.store') }}">
            @csrf
            @if ($kaprodi) @method('PUT') @endif

            {{-- Nama --}}
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium text-stone-700">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $kaprodi?->name) }}" required
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon
                              @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium text-stone-700">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $kaprodi?->email) }}" required
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon
                              @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            @if (!$kaprodi)
                {{-- Password (hanya saat tambah) --}}
                <div class="mb-4">
                    <label class="mb-1 block text-sm font-medium text-stone-700">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required
                           class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon
                                  @error('password') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-stone-400">Min. 8 karakter, huruf besar, huruf kecil, dan angka.</p>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="mb-1 block text-sm font-medium text-stone-700">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                           class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>
            @endif

            {{-- Info read-only --}}
            <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                <strong>Info:</strong> Akun Kaprodi hanya dapat <em>melihat</em> data. Tidak dapat menambah, mengubah, atau menghapus data apapun.
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.super.kaprodi') }}"
                   class="rounded-lg border border-stone-300 px-4 py-2 text-sm text-stone-600 hover:bg-stone-50">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white transition hover:bg-maroon-dark">
                    {{ $kaprodi ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
@endsection
