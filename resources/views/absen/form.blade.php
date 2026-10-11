@php
    $berhasil = session('berhasil');
    $sudah    = session('sudah');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi — {{ $kegiatan->nama }}</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Noto+Serif:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @if ($siteKey && $state === 'aktif' && !$berhasil && !$sudah)
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <style>body{font-family:'Plus Jakarta Sans',sans-serif}.serif{font-family:'Noto Serif',serif}.mono{font-family:'JetBrains Mono',monospace}</style>
</head>
<body class="min-h-screen bg-[#FAF6F5] text-stone-800">

    <header class="bg-[#4A0012] px-4 py-5 text-center text-white">
        <p class="text-xs uppercase tracking-widest text-white/60">Absensi Kegiatan</p>
        <h1 class="serif mt-1 text-xl font-bold leading-snug">{{ $kegiatan->nama }}</h1>
        @if ($kegiatan->lokasi)
            <p class="mt-1 text-sm text-white/70">{{ $kegiatan->lokasi }}</p>
        @endif
    </header>

    <main class="mx-auto w-full max-w-md px-4 py-6">

        @if ($berhasil)
            <div class="rounded-2xl border border-emerald-200 bg-white p-6 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-3xl text-emerald-700">&#10003;</div>
                <h2 class="serif text-xl font-bold text-emerald-800">Absensi tercatat</h2>
                <p class="mt-3 font-semibold">{{ $berhasil['nama'] }}</p>
                <p class="mono text-sm text-stone-500">{{ $berhasil['nim'] }}</p>
                <p class="mt-3 text-sm text-stone-500">{{ $berhasil['sesi'] }} · pukul {{ $berhasil['waktu'] }} WIB</p>
                @if ($berhasil['tamu'])
                    <p class="mt-3 rounded-lg bg-amber-50 p-2 text-xs text-amber-800">Kamu tercatat sebagai tamu karena NIM belum terdaftar sebagai anggota HIMA.</p>
                @endif
            </div>

        @elseif ($sudah)
            <div class="rounded-2xl border border-sky-200 bg-white p-6 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-sky-100 text-3xl text-sky-700">i</div>
                <h2 class="serif text-xl font-bold text-sky-800">Kamu sudah absen</h2>
                <p class="mt-2 text-sm text-stone-600">
                    {{ $sudah['nama'] }} sudah tercatat
                    {{ ($sudah['status'] ?? 'hadir') === 'hadir' ? 'hadir' : 'dengan status ' . ucfirst($sudah['status']) }}
                    pada {{ $sudah['sesi'] }}.
                </p>
            </div>

        @elseif ($state === 'aktif')
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <p class="mb-4 inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                    {{ $sesiAktif->nama_sesi }} · dibuka sampai {{ $sesiAktif->waktu_selesai->format('H:i') }} WIB
                </p>

                @if (session('info'))
                    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">{{ session('info') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('absen.store', $kegiatan->token) }}">
                    @csrf
                    <input type="hidden" name="via" value="{{ old('via', $via) }}">

                    <div class="mb-4">
                        <label for="nim" class="mb-1 block text-sm font-medium">NIM</label>
                        <input id="nim" type="text" name="nim" value="{{ old('nim') }}" required autofocus
                               maxlength="10" inputmode="numeric" pattern="[0-9]*" autocomplete="off"
                               class="mono w-full rounded-lg border border-stone-300 px-4 py-3 text-lg tracking-wider focus:outline-none focus:ring-2 focus:ring-[#6B1124]"
                               placeholder="mis. 525241018">
                    </div>

                    @if ($butuhNama)
                        <div class="mb-4">
                            <label for="nama" class="mb-1 block text-sm font-medium">Nama lengkap (tamu)</label>
                            <input id="nama" type="text" name="nama" value="{{ old('nama') }}" required maxlength="255"
                                   class="w-full rounded-lg border border-stone-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#6B1124]">
                        </div>
                    @endif

                    @if ($siteKey)
                        <div class="mb-4 flex justify-center"><div class="g-recaptcha" data-sitekey="{{ $siteKey }}"></div></div>
                    @endif

                    <button type="submit"
                            class="w-full rounded-lg bg-[#6B1124] py-3 font-semibold text-white transition hover:bg-[#4A0012] active:scale-95">
                        Absen Sekarang
                    </button>
                </form>
            </div>

        @elseif ($state === 'belum')
            <div class="rounded-2xl border border-stone-200 bg-white p-6 text-center shadow-sm">
                <h2 class="serif text-xl font-bold">Absensi belum dibuka</h2>
                <p class="mt-2 text-sm text-stone-600">
                    {{ $sesiBerikut->nama_sesi }} dibuka
                    {{ $sesiBerikut->waktu_mulai->locale('id')->translatedFormat('l, d F Y') }}
                    pukul {{ $sesiBerikut->waktu_mulai->format('H:i') }} WIB.
                </p>
            </div>

        @elseif ($state === 'tutup')
            <div class="rounded-2xl border border-stone-200 bg-white p-6 text-center shadow-sm">
                <h2 class="serif text-xl font-bold">Kegiatan telah ditutup</h2>
                <p class="mt-2 text-sm text-stone-600">Absensi untuk kegiatan ini sudah tidak diterima.</p>
            </div>

        @else
            <div class="rounded-2xl border border-stone-200 bg-white p-6 text-center shadow-sm">
                <h2 class="serif text-xl font-bold">Absensi sudah berakhir</h2>
                <p class="mt-2 text-sm text-stone-600">Seluruh sesi absensi kegiatan ini telah selesai.</p>
            </div>
        @endif

        <p class="mt-6 text-center text-xs text-stone-400">SI HIMA Informatika UNILAM</p>
    </main>
</body>
</html>
