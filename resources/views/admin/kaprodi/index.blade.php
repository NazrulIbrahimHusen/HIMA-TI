@extends('layouts.admin')

@section('title', 'Kelola Kaprodi')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-serif text-2xl font-bold text-stone-800">Akun Kaprodi</h2>
            <p class="text-sm text-stone-500">Kelola akun Kaprodi yang dapat melihat data HIMA secara read-only.</p>
        </div>
        <a href="{{ route('admin.super.kaprodi.create') }}"
           class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white transition hover:bg-maroon-dark">
            + Tambah Kaprodi
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.super.kaprodi') }}"
          class="mb-4 grid grid-cols-1 gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-3">
        <div class="sm:col-span-2">
            <label class="mb-1 block text-xs font-medium text-stone-500">Cari (nama / email)</label>
            <input type="search" name="cari" value="{{ request('cari') }}" placeholder="Ketik untuk mencari…"
                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500">Status</label>
            <select name="status" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="">Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                <option value="semua" @selected(request('status') === 'semua')>Semua</option>
            </select>
        </div>
        <div class="flex items-end gap-2 sm:col-span-3">
            <button type="submit" class="rounded-lg bg-stone-800 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">Terapkan</button>
            <a href="{{ route('admin.super.kaprodi') }}" class="rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-600 hover:bg-stone-50">Reset</a>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-xs uppercase text-stone-500">
                <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($kaprodi_list as $akun)
                    <tr class="{{ $akun->trashed() ? 'opacity-50' : '' }}">
                        <td class="px-4 py-3 font-medium text-stone-800">{{ $akun->name }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ $akun->email }}</td>
                        <td class="px-4 py-3">
                            @if ($akun->trashed())
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Nonaktif</span>
                            @else
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($akun->trashed())
                                <form method="POST" action="{{ route('admin.super.kaprodi.restore', $akun->id) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs text-emerald-600 hover:underline">Aktifkan</button>
                                </form>
                            @else
                                <a href="{{ route('admin.super.kaprodi.edit', $akun) }}"
                                   class="mr-3 text-xs text-maroon hover:underline">Edit</a>

                                {{-- Reset Password Modal Trigger --}}
                                <button type="button"
                                        onclick="document.getElementById('modal-reset-{{ $akun->id }}').classList.remove('hidden')"
                                        class="mr-3 text-xs text-amber-600 hover:underline">
                                    Reset PW
                                </button>

                                <form method="POST" action="{{ route('admin.super.kaprodi.destroy', $akun) }}" class="inline"
                                      onsubmit="return confirm('Nonaktifkan akun {{ $akun->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:underline">Nonaktifkan</button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    {{-- Modal Reset Password --}}
                    @unless ($akun->trashed())
                        <div id="modal-reset-{{ $akun->id }}" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
                            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                                <h3 class="mb-4 font-serif text-lg font-bold text-stone-800">Reset Password — {{ $akun->name }}</h3>
                                <form method="POST" action="{{ route('admin.super.kaprodi.reset-password', $akun) }}">
                                    @csrf @method('PATCH')
                                    <div class="mb-3">
                                        <label class="mb-1 block text-xs font-medium text-stone-500">Password Baru</label>
                                        <input type="password" name="password" required
                                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                                    </div>
                                    <div class="mb-4">
                                        <label class="mb-1 block text-xs font-medium text-stone-500">Konfirmasi Password</label>
                                        <input type="password" name="password_confirmation" required
                                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button"
                                                onclick="document.getElementById('modal-reset-{{ $akun->id }}').classList.add('hidden')"
                                                class="rounded-lg border border-stone-300 px-4 py-2 text-sm text-stone-600 hover:bg-stone-50">
                                            Batal
                                        </button>
                                        <button type="submit" class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-dark">
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endunless
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-stone-400">Belum ada akun Kaprodi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($kaprodi_list->hasPages())
            <div class="border-t border-stone-100 px-4 py-3">
                {{ $kaprodi_list->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
