@extends('layouts.admin')

@section('title', $admin ? 'Edit Admin' : 'Tambah Admin')

@section('content')
<div class="mx-auto max-w-lg">

    {{-- Breadcrumb --}}
    <nav class="mb-5 flex items-center gap-1.5 text-sm text-stone-500">
        <a href="{{ route('admin.super.akun') }}" class="hover:text-maroon transition">Kelola Admin</a>
        <svg class="h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="font-medium text-stone-700">{{ $admin ? 'Edit Admin' : 'Tambah Admin' }}</span>
    </nav>

    <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 font-serif text-xl font-bold text-stone-800">
            {{ $admin ? 'Edit Akun Admin' : 'Tambah Akun Admin Baru' }}
        </h2>

        <form method="POST"
              action="{{ $admin ? route('admin.super.akun.update', $admin) : route('admin.super.akun.store') }}"
              class="space-y-5">
            @csrf
            @if($admin)
                @method('PUT')
            @endif

            {{-- Nama --}}
            <div>
                <label for="name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-600">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name"
                       value="{{ old('name', $admin?->name) }}"
                       placeholder="Contoh: Budi Santoso"
                       class="w-full rounded-lg border px-3 py-2 text-sm transition focus:outline-none
                              {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-stone-300 focus:border-maroon focus:ring-1 focus:ring-maroon/30' }}">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-600">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email"
                       value="{{ old('email', $admin?->email) }}"
                       placeholder="admin@hima-ti.com"
                       class="w-full rounded-lg border px-3 py-2 text-sm transition focus:outline-none
                              {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-stone-300 focus:border-maroon focus:ring-1 focus:ring-maroon/30' }}">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

                       {{-- Password (hanya saat tambah baru) --}}
            @if(!$admin)
                <div x-data="{ show: false }">
                    <label for="password" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-600">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password" name="password"
                               placeholder="Min. 8 karakter, huruf besar + angka"
                               class="w-full rounded-lg border px-3 py-2 pr-10 text-sm transition focus:outline-none
                                      {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-stone-300 focus:border-maroon focus:ring-1 focus:ring-maroon/30' }}">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-stone-400 hover:text-stone-600 transition"
                                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                            {{-- Mata terbuka --}}
                            <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{-- Mata dicoret --}}
                            <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ show: false }">
                    <label for="password_confirmation" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-600">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation"
                               placeholder="Ulangi password"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 pr-10 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon/30 transition">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-stone-400 hover:text-stone-600 transition"
                                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                            {{-- Mata terbuka --}}
                            <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{-- Mata dicoret --}}
                            <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                </div>

               

                <p class="rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-700">
                    Admin baru langsung bisa login dengan password ini. Gunakan fitur
                    <strong>Reset Password</strong> di tabel jika ingin memaksa admin ganti password saat login.
                </p>
            @else
                <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700">
                    Untuk mengubah password admin, gunakan tombol <strong>Reset Password</strong> di halaman daftar admin.
                </p>
            @endif

            {{-- Tombol aksi --}}
            <div class="flex items-center justify-end gap-3 border-t border-stone-100 pt-4">
                <a href="{{ route('admin.super.akun') }}"
                   class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-600 hover:bg-stone-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-maroon px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-maroon-dark transition">
                    {{ $admin ? 'Simpan Perubahan' : 'Tambah Admin' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
