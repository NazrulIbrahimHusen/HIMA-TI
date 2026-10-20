@extends('layouts.mahasiswa')

@section('title', 'KTA Digital')

@push('head')
<script src="{{ asset('js/html2canvas.min.js') }}"></script>
@endpush

@section('content')
@php
    $logoPath = public_path('images/logo-hima.png');
    $logoB64  = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    $fotoB64 = null;
    if ($anggota?->foto) {
        $fotoPath = storage_path('app/public/' . $anggota->foto);
        if (file_exists($fotoPath)) {
            $mime    = mime_content_type($fotoPath);
            $fotoB64 = "data:{$mime};base64," . base64_encode(file_get_contents($fotoPath));
        }
    }
@endphp

<div class="mx-auto max-w-sm space-y-5">

    <p class="text-sm text-stone-500 text-center">Kartu Tanda Anggota digital HIMA Informatika UNILAM.</p>

    {{-- KTA Card — semua warna pakai inline style, bukan Tailwind, agar html2canvas bisa baca --}}
    <div id="kta-card" style="
        background: linear-gradient(135deg, #4A0012 0%, #6B1124 55%, #8B1A2E 100%);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 4px 24px rgba(0,0,0,0.3);
        min-height: 200px;
        user-select: none;
        font-family: inherit;
    ">
        {{-- Pola dekoratif --}}
        <div style="position:absolute;inset:0;opacity:0.1;pointer-events:none;">
            <div style="position:absolute;right:-40px;top:-40px;width:192px;height:192px;border-radius:50%;border:2px solid white;"></div>
            <div style="position:absolute;right:-16px;top:-16px;width:144px;height:144px;border-radius:50%;border:2px solid white;"></div>
            <div style="position:absolute;bottom:-32px;left:-32px;width:128px;height:128px;border-radius:50%;border:2px solid white;"></div>
        </div>

        {{-- Konten --}}
        <div style="position:relative;z-index:10;padding:20px;">

            {{-- Header --}}
            <div style="display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(255,255,255,0.2);padding-bottom:12px;">
                @if($logoB64)
                    <img src="{{ $logoB64 }}" style="width:40px;height:40px;object-fit:contain;" alt="HIMA">
                @else
                    <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:12px;">HI</div>
                @endif
                <div style="line-height:1.3;">
                    <p style="color:white;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;margin:0;">HIMA Informatika</p>
                    <p style="color:rgba(255,255,255,0.6);font-size:10px;margin:0;">Universitas La Tansa Mashiro</p>
                </div>
                <span style="margin-left:auto;background:rgba(255,255,255,0.15);color:white;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;padding:2px 8px;border-radius:999px;">
                    Anggota
                </span>
            </div>

            {{-- Body --}}
            <div style="display:flex;align-items:center;gap:16px;margin-top:12px;">
                {{-- Foto --}}
                <div style="width:80px;height:80px;flex-shrink:0;border-radius:12px;border:2px solid rgba(255,255,255,0.3);background:rgba(255,255,255,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    @if($fotoB64)
                        <img src="{{ $fotoB64 }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                    @else
                        <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.4)" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                    @endif
                </div>

                {{-- Data --}}
                <div style="flex:1;color:white;">
                    <p style="font-size:18px;font-weight:700;margin:0 0 4px;line-height:1.2;">{{ $user->name }}</p>
                    <p style="font-family:monospace;font-size:13px;color:rgba(255,255,255,0.8);margin:0 0 4px;letter-spacing:0.05em;">{{ $user->nim }}</p>
                    @if($anggota)
                        <p style="font-size:12px;color:rgba(255,255,255,0.7);margin:0 0 2px;">{{ $anggota->prodi }}</p>
                        <p style="font-size:11px;color:rgba(255,255,255,0.6);margin:0;">Angkatan {{ $anggota->angkatan }}</p>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(255,255,255,0.2);margin-top:16px;padding-top:10px;">
                <p style="font-size:9px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.15em;margin:0;">Kartu Tanda Anggota</p>
                @if($anggota)
                    <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;
                        {{ $anggota->status === 'aktif'
                            ? 'background:rgba(52,211,153,0.2);color:#6ee7b7;'
                            : 'background:rgba(248,113,113,0.2);color:#fca5a5;' }}">
                        {{ ucfirst($anggota->status) }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Tombol unduh --}}
    <div class="flex flex-col gap-3">
        <button id="btn-unduh-png"
                class="flex items-center justify-center gap-2 rounded-xl bg-[#6B1124] py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#4A0012] transition active:scale-95">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Unduh KTA (PNG)
        </button>

        @if(!$anggota?->foto)
        <a href="{{ route('mahasiswa.profil') }}"
           class="text-center text-xs text-[#6B1124] hover:underline">
            + Tambah foto profil untuk KTA yang lebih lengkap
        </a>
        @endif
    </div>

    <p class="text-center text-xs text-stone-400">
        KTA ini hanya berlaku sebagai identitas digital anggota HIMA Informatika UNILAM.
    </p>

</div>
@endsection

@push('scripts')
<script>
document.getElementById('btn-unduh-png').addEventListener('click', function () {
    const btn  = this;
    const card = document.getElementById('kta-card');

    btn.disabled    = true;
    btn.textContent = 'Memproses…';

    html2canvas(card, {
        scale          : 2,
        useCORS        : false,
        allowTaint     : false,
        backgroundColor: null,
        logging        : false,
        onclone        : function (doc) {
            // Pastikan font sudah load sebelum render
            return document.fonts.ready;
        }
    }).then(function (canvas) {
        const link    = document.createElement('a');
        link.download = 'KTA-HIMA-{{ $user->nim }}.png';
        link.href     = canvas.toDataURL('image/png');
        link.click();

        btn.disabled  = false;
        btn.innerHTML = `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
        </svg> Unduh KTA (PNG)`;
    }).catch(function (err) {
        console.error(err);
        btn.disabled    = false;
        btn.textContent = 'Gagal. Coba lagi.';
    });
});
</script>
@endpush