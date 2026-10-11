<?php

return [
    // Dipakai di header export Excel/PDF
    'nama'        => env('HIMA_NAMA', 'HIMA Informatika UNILAM'),
    // Letakkan file logo (PNG) di public/images/logo-hima.png. Jika tidak ada, header tampil tanpa logo.
    'logo'        => 'images/logo-hima.png',
    // Jabatan penandatangan di bagian bawah PDF
    'jabatan_ttd' => env('HIMA_JABATAN_TTD', 'Ketua HIMA Informatika'),
];
