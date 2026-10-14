<!DOCTYPE html>
<html lang="id" class="paksa-terang">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Absensi — {{ $kegiatan->nama }}</title>
    <style>svg{max-width:100%;height:auto}</style>
    @include('layouts._tema')
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-white px-4 py-8 text-center">
    <p class="mb-2 text-sm font-semibold uppercase tracking-widest text-[#6B1124]">Absensi Kegiatan</p>
    <h1 class="serif mb-6 max-w-3xl text-3xl font-bold text-stone-800 sm:text-4xl">{{ $kegiatan->nama }}</h1>

    <div class="mb-6 rounded-2xl border-4 border-[#6B1124] p-4">{!! $qrSvg !!}</div>

    <p class="mb-1 text-lg font-semibold text-stone-700">Scan QR dengan kamera HP, lalu isi NIM</p>
    <p class="mb-5 break-all font-mono text-sm text-stone-400">{{ $linkAbsen }}</p>

    <div class="space-y-1 text-sm text-stone-500">
        @foreach ($kegiatan->sesiAbsensi as $s)
            <p>{{ $s->nama_sesi }} · {{ $s->waktu_mulai->locale('id')->translatedFormat('d M, H:i') }} – {{ $s->waktu_selesai->format('H:i') }} WIB</p>
        @endforeach
    </div>

    <a href="{{ route('admin.kegiatan.show', $kegiatan) }}" class="mt-8 text-xs text-stone-400 hover:underline print:hidden">&larr; Kembali ke panel</a>
</body>
</html>
