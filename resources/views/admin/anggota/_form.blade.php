@php $edit = isset($anggota); @endphp

@if ($errors->any())
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="nim" class="mb-1 block text-sm font-medium text-stone-700">NIM</label>
        <input id="nim" type="text" name="nim" value="{{ old('nim', $anggota->nim ?? '') }}"
               maxlength="10" inputmode="numeric" pattern="[0-9]*" required @readonly($edit)
               class="w-full rounded-lg border border-stone-300 px-3 py-2.5 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-maroon {{ $edit ? 'bg-stone-100 text-stone-500' : '' }}"
               placeholder="mis. 525241018">
        <p class="mt-1 text-xs text-stone-400">
            {{ $edit ? 'NIM tidak dapat diubah karena dipakai untuk login.' : 'Angka saja, maksimal 10 digit. Password awal = NIM.' }}
        </p>
    </div>

    <div>
        <label for="nama" class="mb-1 block text-sm font-medium text-stone-700">Nama Lengkap</label>
        <input id="nama" type="text" name="nama" value="{{ old('nama', $anggota->nama ?? '') }}" required maxlength="255"
               class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
    </div>

    <div>
        <label for="prodi" class="mb-1 block text-sm font-medium text-stone-700">Program Studi</label>
        <input id="prodi" type="text" name="prodi" list="prodi-list" value="{{ old('prodi', $anggota->prodi ?? '') }}" required maxlength="100"
               class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon"
               placeholder="mis. Informatika">
        <datalist id="prodi-list">
            @foreach ($prodiList as $p)
                <option value="{{ $p }}">
            @endforeach
        </datalist>
    </div>

    <div>
        <label for="status" class="mb-1 block text-sm font-medium text-stone-700">Status</label>
        <select id="status" name="status"
                class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
            <option value="aktif" @selected(old('status', $anggota->status ?? 'aktif') === 'aktif')>Aktif</option>
            <option value="tidak_aktif" @selected(old('status', $anggota->status ?? 'aktif') === 'tidak_aktif')>Tidak aktif</option>
        </select>
    </div>

    <div>
        <label for="angkatan" class="mb-1 block text-sm font-medium text-stone-700">Angkatan</label>
        <input id="angkatan" type="number" name="angkatan" min="2000" max="{{ date('Y') + 1 }}" required
               value="{{ old('angkatan', $anggota->angkatan ?? date('Y')) }}"
               class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
    </div>

    <div>
        <label for="semester" class="mb-1 block text-sm font-medium text-stone-700">Semester</label>
        <input id="semester" type="number" name="semester" min="1" max="14" required
               value="{{ old('semester', $anggota->semester ?? 1) }}"
               class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
    </div>

    <div class="sm:col-span-2">
        <label for="foto" class="mb-1 block text-sm font-medium text-stone-700">Foto Profil <span class="font-normal text-stone-400">(opsional)</span></label>
        <div class="flex items-center gap-4">
            @if ($edit && $anggota->foto)
                <img src="{{ asset('storage/' . $anggota->foto) }}" alt="" class="h-16 w-16 rounded-full object-cover">
            @endif
            <input id="foto" type="file" name="foto" accept="image/png,image/jpeg"
                   class="block w-full text-sm text-stone-600 file:mr-4 file:rounded-lg file:border-0 file:bg-maroon-soft file:px-4 file:py-2 file:text-sm file:font-semibold file:text-maroon hover:file:bg-[#f0dce0]">
        </div>
        <p class="mt-1 text-xs text-stone-400">JPG atau PNG, maksimal 2 MB.</p>
    </div>
</div>
