@php
    $edit = isset($kegiatan);

    $default = $edit
        ? $kegiatan->sesiAbsensi->sortBy('waktu_mulai')->map(fn ($s) => [
            'id'            => $s->id,
            'nama_sesi'     => $s->nama_sesi,
            'waktu_mulai'   => $s->waktu_mulai->format('Y-m-d\TH:i'),
            'waktu_selesai' => $s->waktu_selesai->format('Y-m-d\TH:i'),
        ])->values()->all()
        : [['id' => '', 'nama_sesi' => 'Sesi 1', 'waktu_mulai' => '', 'waktu_selesai' => '']];

    $sesiInit = array_values(old('sesi', $default));
@endphp

@if ($errors->any())
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc space-y-0.5">
            @foreach (array_unique($errors->all()) as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="nama" class="mb-1 block text-sm font-medium text-stone-700">Nama Kegiatan</label>
        <input id="nama" type="text" name="nama" value="{{ old('nama', $kegiatan->nama ?? '') }}" required maxlength="255"
               class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
    </div>

    <div>
        <label for="tipe" class="mb-1 block text-sm font-medium text-stone-700">Tipe</label>
        <select id="tipe" name="tipe" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
            <option value="internal" @selected(old('tipe', $kegiatan->tipe ?? 'internal') === 'internal')>Internal</option>
            <option value="kolaborasi" @selected(old('tipe', $kegiatan->tipe ?? 'internal') === 'kolaborasi')>Kolaborasi / eksternal</option>
        </select>
    </div>

    <div>
        <label for="lokasi" class="mb-1 block text-sm font-medium text-stone-700">Lokasi <span class="font-normal text-stone-400">(opsional)</span></label>
        <input id="lokasi" type="text" name="lokasi" value="{{ old('lokasi', $kegiatan->lokasi ?? '') }}" maxlength="255"
               class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
    </div>

    <div class="sm:col-span-2">
        <label for="deskripsi" class="mb-1 block text-sm font-medium text-stone-700">Deskripsi <span class="font-normal text-stone-400">(opsional)</span></label>
        <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="5000"
                  class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">{{ old('deskripsi', $kegiatan->deskripsi ?? '') }}</textarea>
    </div>
</div>

{{-- Sesi absensi (dinamis) --}}
<div class="mt-7" x-data="{ sesi: {{ \Illuminate\Support\Js::from($sesiInit) }} }">
    <div class="mb-2 flex items-center justify-between">
        <div>
            <h3 class="font-serif text-lg font-bold text-stone-800">Sesi Absensi</h3>
            <p class="text-xs text-stone-500">Absensi hanya bisa diisi saat sesi berlangsung. Untuk status "hadir", peserta harus hadir di semua sesi.</p>
        </div>
        <button type="button" @click="sesi.push({ id: '', nama_sesi: 'Sesi ' + (sesi.length + 1), waktu_mulai: '', waktu_selesai: '' })"
                x-show="sesi.length < 10"
                class="rounded-lg border border-maroon px-3 py-1.5 text-xs font-semibold text-maroon hover:bg-maroon-soft">
            + Tambah Sesi
        </button>
    </div>

    <div class="space-y-3">
        <template x-for="(s, i) in sesi" :key="i">
            <div class="grid grid-cols-1 gap-3 rounded-xl border border-stone-200 bg-stone-50 p-4 sm:grid-cols-7">
                <input type="hidden" :name="`sesi[${i}][id]`" :value="s.id">

                <div class="sm:col-span-3">
                    <label class="mb-1 block text-xs font-medium text-stone-500">Nama sesi</label>
                    <input type="text" :name="`sesi[${i}][nama_sesi]`" x-model="s.nama_sesi" required maxlength="100"
                           class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-stone-500">Mulai</label>
                    <input type="datetime-local" :name="`sesi[${i}][waktu_mulai]`" x-model="s.waktu_mulai" required
                           class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-stone-500">Selesai</label>
                    <input type="datetime-local" :name="`sesi[${i}][waktu_selesai]`" x-model="s.waktu_selesai" required
                           class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                </div>

                <div class="sm:col-span-7 -mt-1 text-right" x-show="sesi.length > 1">
                    <button type="button" @click="sesi.splice(i, 1)" class="text-xs font-semibold text-red-700 hover:underline">Hapus sesi ini</button>
                </div>
            </div>
        </template>
    </div>
</div>
