{{--
    Tema terang/gelap otomatis mengikuti pengaturan perangkat (prefers-color-scheme).
    Aturan warna ada di resources/css/app.css. Aset dibangun oleh Vite: `npm run build` (atau `npm run dev`).
--}}
<meta name="color-scheme" content="light dark">
<meta name="theme-color" media="(prefers-color-scheme: light)" content="#FAF6F5">
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="#120d0e">
<link rel="icon" type="image/png" href="{{ asset('images/logo-hima.png') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
