@php
    $label   = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];
    $multi   = $sesi->count() > 1;
    $mulai   = $kegiatan->tanggal_mulai->locale('id');
    $selesai = $kegiatan->tanggal_selesai->locale('id');
    $tanggal = $mulai->translatedFormat('d F Y') . ($mulai->isSameDay($selesai) ? '' : ' – ' . $selesai->translatedFormat('d F Y'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi — {{ $kegiatan->nama }}</title>
    <style>
        @page { margin: 28px 34px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        table { border-collapse: collapse; }
        .kop { width: 100%; border-bottom: 2px solid #6B1124; padding-bottom: 6px; }
        .kop td { vertical-align: middle; }
        .org { font-size: 15px; font-weight: bold; color: #6B1124; }
        .judul { font-size: 11px; font-weight: bold; margin-top: 2px; }
        .info { margin-top: 10px; width: 100%; }
        .info td { padding: 1px 0; vertical-align: top; }
        .info td.k { width: 78px; color: #555; }
        table.data { width: 100%; margin-top: 10px; }
        table.data th { background: #6B1124; color: #fff; border: 1px solid #6B1124; padding: 5px 4px; font-size: 9px; }
        table.data td { border: 1px solid #999; padding: 4px; }
        .c { text-align: center; }
        .ringkas { margin-top: 8px; font-size: 9px; color: #444; }
        .ttd { width: 100%; margin-top: 28px; page-break-inside: avoid; }
        .ttd td { vertical-align: top; }
        .ruang { height: 70px; }
    </style>
</head>
<body>

    <table class="kop">
        <tr>
            @if ($logo)
                <td style="width: 70px;"><img src="{{ $logo }}" style="height: 56px;" alt=""></td>
            @endif
            <td>
                <div class="org">{{ mb_strtoupper(config('hima.nama')) }}</div>
                <div class="judul">REKAP ABSENSI KEGIATAN</div>
            </td>
        </tr>
    </table>

    <table class="info">
        <tr><td class="k">Kegiatan</td><td>: <strong>{{ $kegiatan->nama }}</strong></td></tr>
        <tr><td class="k">Tanggal</td><td>: {{ $tanggal }}</td></tr>
        @if ($kegiatan->lokasi)
            <tr><td class="k">Lokasi</td><td>: {{ $kegiatan->lokasi }}</td></tr>
        @endif
        <tr><td class="k">Dicetak</td><td>: {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB</td></tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 24px;">No</th>
                <th style="width: 62px;">NIM</th>
                <th>Nama</th>
                <th style="width: 44px;">Peserta</th>
                @foreach ($sesi as $s)
                    <th>{{ $s->nama_sesi }}</th>
                @endforeach
                @if ($multi)
                    <th>Kehadiran</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($baris as $b)
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td class="c">{{ $b['nim'] }}</td>
                    <td>{{ $b['nama'] }}</td>
                    <td class="c">{{ $b['tipe'] === 'anggota' ? 'Anggota' : 'Tamu' }}</td>
                    @foreach ($sesi as $s)
                        <td class="c">{{ $label[$b['status'][$s->id] ?? ''] ?? '-' }}</td>
                    @endforeach
                    @if ($multi)
                        <td class="c">{{ $b['hadir_semua'] ? 'Hadir' : 'Tidak hadir (' . $b['jumlah_hadir'] . '/' . $sesi->count() . ')' }}</td>
                    @endif
                </tr>
            @empty
                <tr><td class="c" colspan="{{ 4 + $sesi->count() + ($multi ? 1 : 0) }}">Belum ada data absensi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="ringkas">
        Total peserta: {{ $ringkasan['total'] }} · Hadir: {{ $ringkasan['hadir'] }} · Tidak hadir: {{ $ringkasan['tidak_hadir'] }} · Tamu: {{ $ringkasan['tamu'] }}
    </p>

    <table class="ttd">
        <tr>
            <td style="width: 55%;"></td>
            <td>
                <div>{{ now()->locale('id')->translatedFormat('d F Y') }}</div>
                <div>{{ config('hima.jabatan_ttd') }}</div>
                <div class="ruang"></div>
                <div>( ................................................ )</div>
            </td>
        </tr>
    </table>

</body>
</html>
