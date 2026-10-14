@props(['tinggi' => 'h-10', 'bulat' => false])

{{-- Logo HIMA selalu di atas latar putih agar tetap terbaca di tema terang maupun gelap --}}
<span {{ $attributes->class(['logo-badge inline-flex shrink-0 items-center justify-center bg-white p-1.5 shadow-sm', $bulat ? 'rounded-full' : 'rounded-xl']) }}>
    <img src="{{ asset('images/logo-hima.png') }}" alt="Logo HIMA Informatika" class="{{ $tinggi }} w-auto">
</span>
