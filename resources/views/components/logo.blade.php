@props(['tinggi' => 'h-10', 'bulat' => false, 'besar' => false])

{{-- Logo HIMA selalu di atas latar putih agar tetap terbaca di tema terang maupun gelap --}}
<span {{ $attributes->class([
    'logo-badge inline-flex shrink-0 items-center justify-center bg-white shadow-sm',
    $besar ? 'rounded-3xl p-5 shadow-xl' : ($bulat ? 'rounded-full p-1.5' : 'rounded-xl p-1.5'),
]) }}>
    <img src="{{ asset('images/logo-hima.png') }}" alt="Logo HIMA Informatika" class="{{ $tinggi }} w-auto">
</span>
