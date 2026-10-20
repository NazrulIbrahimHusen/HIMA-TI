@extends('layouts.admin')

@section('title', 'Kelola Admin')

@section('content')
<div class="space-y-6">

    {{-- Header + tombol tambah --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-stone-500">Kelola akun admin yang dapat mengakses panel ini.</p>
        <a href="{{ route('admin.super.akun.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-maroon-dark">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Admin
        </a>
    </div>

    {{-- Filter & pencarian --}}
    <form method="GET" action="{{ route('admin.super.akun') }}"
          class="flex flex-wrap gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
        <input type="text" name="cari" value="{{ request('cari') }}"
               placeholder="Cari nama atau email…"
               class="flex-1 min-w-52 rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm placeholder-stone-400 focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon/30">

        <select name="status"
                class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon/30">
            <option value=""        {{ request('status') === ''        ? 'selected' : '' }}>Aktif</option>
            <option value="semua"   {{ request('status') === 'semua'   ? 'selected' : '' }}>Semua</option>
            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>

        <button type="submit"
                class="rounded-lg bg-maroon px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-dark transition">
            Cari
        </button>
        @if(request('cari') || request('status'))
            <a href="{{ route('admin.super.akun') }}"
               class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-600 hover:bg-stone-50 transition">
                Reset
            </a>
        @endif
    </form>

    {{-- Tabel admin --}}
    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        @if($admins->isEmpty())
            <div class="flex flex-col items-center justify-center gap-2 py-16 text-stone-400">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                <p class="text-sm font-medium">Tidak ada data admin ditemukan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#FBF4F3] text-[11px] font-bold uppercase tracking-wider text-stone-500">
                        <tr>
                            <th class="px-5 py-3 text-left">#</th>
                            <th class="px-5 py-3 text-left">Nama</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Bergabung</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($admins as $i => $admin)
                        <tr class="{{ $admin->trashed() ? 'bg-stone-50 opacity-60' : 'hover:bg-[#FDF8F7]' }} transition">
                            <td class="px-5 py-3 font-mono text-stone-400">
                                {{ $admins->firstItem() + $i }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon/10 text-xs font-bold text-maroon">
                                        {{ strtoupper(mb_substr($admin->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-stone-800">{{ $admin->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-stone-600">{{ $admin->email }}</td>
                            <td class="px-5 py-3">
                                @if($admin->trashed())
                                    <span class="inline-flex items-center rounded-full bg-stone-100 px-2.5 py-0.5 text-[11px] font-semibold text-stone-500">
                                        Nonaktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-stone-500">
                                {{ $admin->created_at->locale('id')->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @if($admin->trashed())
                                        <form method="POST"
                                              action="{{ route('admin.super.akun.restore', $admin->id) }}"
                                              onsubmit="return confirm('Aktifkan kembali akun {{ addslashes($admin->name) }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        {{-- Edit --}}
                                        <a href="{{ route('admin.super.akun.edit', $admin) }}"
                                           class="rounded-lg border border-stone-300 p-1.5 text-stone-600 hover:bg-stone-50 transition"
                                           title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                            </svg>
                                        </a>

                                        {{-- Reset password --}}
                                        <button type="button"
                                                onclick="bukaModalReset({{ $admin->id }}, '{{ addslashes($admin->name) }}')"
                                                class="rounded-lg border border-amber-300 p-1.5 text-amber-600 hover:bg-amber-50 transition"
                                                title="Reset password">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                                            </svg>
                                        </button>

                                        {{-- Nonaktifkan --}}
                                        <form method="POST"
                                              action="{{ route('admin.super.akun.destroy', $admin) }}"
                                              onsubmit="return confirm('Nonaktifkan akun {{ addslashes($admin->name) }}? Admin tidak bisa login sampai diaktifkan kembali.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg border border-red-300 p-1.5 text-red-600 hover:bg-red-50 transition"
                                                    title="Nonaktifkan">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($admins->hasPages())
                <div class="border-t border-stone-100 px-5 py-3">
                    {{ $admins->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Modal Reset Password --}}
<div id="modalReset"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h3 class="mb-1 font-serif text-lg font-bold text-stone-800">Reset Password Admin</h3>
        <p class="mb-5 text-sm text-stone-500">
            Password baru untuk: <strong id="namaAdminReset" class="text-stone-700"></strong>
        </p>

        <form id="formReset" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-600">
                    Password Baru
                </label>
                <input type="password" name="password" id="inputPasswordBaru" required
                       placeholder="Min. 8 karakter, huruf besar + angka"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon/30">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-600">
                    Konfirmasi Password
                </label>
                <input type="password" name="password_confirmation" id="inputPasswordKonfirmasi" required
                       placeholder="Ulangi password baru"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon/30">
            </div>

            <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700">
                Admin akan diminta mengganti password saat login berikutnya.
            </p>

            <div class="flex justify-end gap-3 pt-1">
                <button type="button" onclick="tutupModalReset()"
                        class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-600 hover:bg-stone-50 transition">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition">
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const baseResetUrl = "{{ url('admin/super/akun') }}";

    function bukaModalReset(id, nama) {
        document.getElementById('namaAdminReset').textContent = nama;
        document.getElementById('formReset').action = `${baseResetUrl}/${id}/reset-password`;
        document.getElementById('inputPasswordBaru').value = '';
        document.getElementById('inputPasswordKonfirmasi').value = '';
        document.getElementById('modalReset').classList.remove('hidden');
        document.getElementById('modalReset').classList.add('flex');
    }

    function tutupModalReset() {
        document.getElementById('modalReset').classList.add('hidden');
        document.getElementById('modalReset').classList.remove('flex');
    }

    document.getElementById('modalReset').addEventListener('click', function (e) {
        if (e.target === this) tutupModalReset();
    });
</script>
@endpush
