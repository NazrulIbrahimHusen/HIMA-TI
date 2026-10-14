<?php

return [
    // Nama organisasi (dipakai di header export Excel)
    'nama' => env('HIMA_NAMA', 'Himpunan Mahasiswa Program Studi Informatika'),

    // Kop surat untuk export PDF — mengikuti kop surat resmi HIMA
    'kop' => [
        'baris' => [
            'HIMPUNAN MAHASISWA PROGRAM STUDI INFORMATIKA',
            'FAKULTAS TEKNOLOGI INFORMASI',
            'UNIVERSITAS LA TANSA MASHIRO',
        ],
        'alamat' => env('HIMA_ALAMAT', 'Jl. Soekarno-Hatta, Rangkasbitung, Kabupaten Lebak, Banten 42317'),
        'email'  => env('HIMA_EMAIL', 'himainformatika25@gmail.com'),
    ],

    // Logo (PNG transparan) di public/images
    'logo'        => 'images/logo-hima.png',    // logo HIMA — kanan kop, juga dipakai di Excel
    'logo_kampus' => 'images/logo-unilam.png',  // logo UNILAM — kiri kop

    // Jabatan penandatangan di bagian bawah PDF
    'jabatan_ttd' => env('HIMA_JABATAN_TTD', 'Ketua HIMA Informatika'),
];
