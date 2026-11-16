@extends('layouts.mahasiswa')

@section('title', 'KTA Digital')

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

    // Jabatan mengikuti periode kepengurusan yang aktif ("Anggota" jika bukan pengurus)
    $jabatan = $anggota?->jabatan_label ?? 'Anggota';

    $dataKta = [
        'nama'     => $user->name,
        'nim'      => (string) $user->nim,
        'prodi'    => $anggota->prodi ?? '',
        'angkatan' => $anggota ? 'Angkatan ' . $anggota->angkatan : '',
        'jabatan'  => $jabatan,
        'aktif'    => ($anggota->status ?? '') === 'aktif',
        'logo'     => $logoB64,
        'foto'     => $fotoB64,
    ];
@endphp

<div style="max-width:24rem;margin:0 auto;" class="space-y-5">

    <p class="text-center text-sm text-stone-500">Kartu Tanda Anggota digital HIMA Informatika UNILAM.</p>

    {{-- Pratinjau kartu (HTML biasa, tampilan di layar) --}}
    <div style="
        background: linear-gradient(135deg, #4A0012 0%, #6B1124 55%, #8B1A2E 100%);
        border-radius: 16px; overflow: hidden; position: relative;
        box-shadow: 0 4px 24px rgba(0,0,0,0.3); font-family: Georgia, serif; width: 100%;">

        <div style="position:absolute;top:0;right:0;bottom:0;left:0;opacity:0.1;pointer-events:none;">
            <div style="position:absolute;right:-40px;top:-40px;width:192px;height:192px;border-radius:50%;border:2px solid #fff;"></div>
            <div style="position:absolute;right:-16px;top:-16px;width:144px;height:144px;border-radius:50%;border:2px solid #fff;"></div>
            <div style="position:absolute;bottom:-32px;left:-32px;width:128px;height:128px;border-radius:50%;border:2px solid #fff;"></div>
        </div>

        <div style="position:relative;z-index:10;padding:20px;">
            <div style="display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(255,255,255,0.2);padding-bottom:12px;">
                @if($logoB64)
                    <img src="{{ $logoB64 }}" style="width:40px;height:40px;object-fit:contain;display:block;flex-shrink:0;" alt="">
                @endif
                <div style="min-width:0;">
                    <p style="color:#fff;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;margin:0;">HIMA Informatika</p>
                    <p style="color:rgba(255,255,255,0.6);font-size:10px;margin:2px 0 0;font-family:Arial,sans-serif;">Universitas La Tansa Mashiro</p>
                </div>
                <div style="margin-left:auto;max-width:45%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;background:rgba(255,255,255,0.15);color:#fff;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;padding:3px 10px;border-radius:999px;font-family:Arial,sans-serif;">{{ $jabatan }}</div>
            </div>

            <div style="display:flex;align-items:center;gap:16px;margin-top:14px;">
                <div style="width:80px;height:80px;flex-shrink:0;border-radius:10px;border:2px solid rgba(255,255,255,0.3);background:rgba(255,255,255,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    @if($fotoB64)
                        <img src="{{ $fotoB64 }}" style="width:100%;height:100%;object-fit:cover;display:block;" alt="">
                    @else
                        <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.4)" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="color:#fff;font-size:19px;font-weight:700;margin:0 0 4px;line-height:1.2;word-break:break-word;">{{ $user->name }}</p>
                    <p style="color:rgba(255,255,255,0.8);font-size:13px;margin:0 0 4px;font-family:monospace;letter-spacing:0.05em;">{{ $user->nim }}</p>
                    @if($anggota)
                        <p style="color:rgba(255,255,255,0.7);font-size:12px;margin:0 0 2px;font-family:Arial,sans-serif;">{{ $anggota->prodi }}</p>
                        <p style="color:rgba(255,255,255,0.6);font-size:11px;margin:0;font-family:Arial,sans-serif;">Angkatan {{ $anggota->angkatan }}</p>
                    @endif
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(255,255,255,0.2);margin-top:16px;padding-top:10px;">
                <p style="font-size:9px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.15em;margin:0;font-family:Arial,sans-serif;">Kartu Tanda Anggota</p>
                @if($anggota)
                    @if($anggota->status === 'aktif')
                        <span style="font-size:10px;font-weight:600;padding:2px 10px;border-radius:999px;background:rgba(52,211,153,0.2);color:#6ee7b7;font-family:Arial,sans-serif;">Aktif</span>
                    @else
                        <span style="font-size:10px;font-weight:600;padding:2px 10px;border-radius:999px;background:rgba(248,113,113,0.2);color:#fca5a5;font-family:Arial,sans-serif;">Tidak Aktif</span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-3">
        <button type="button" id="btn-unduh-png"
                class="flex items-center justify-center gap-2 rounded-xl bg-maroon py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-maroon-dark active:scale-95">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            <span id="btn-unduh-label">Unduh KTA (PNG)</span>
        </button>

        @if(!$anggota?->foto)
            <a href="{{ route('mahasiswa.profil') }}" class="text-center text-xs text-maroon hover:underline">
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
{{-- Unduh PNG: digambar langsung dengan Canvas bawaan browser (tanpa library / CDN) --}}
<script>
(function () {
    const D = @json($dataKta);

    function muatGambar(src) {
        return new Promise(function (resolve) {
            if (!src) return resolve(null);
            const img = new Image();
            img.onload  = function () { resolve(img); };
            img.onerror = function () { resolve(null); };
            img.src = src;
        });
    }

    function rr(ctx, x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.arcTo(x + w, y,     x + w, y + h, r);
        ctx.arcTo(x + w, y + h, x,     y + h, r);
        ctx.arcTo(x,     y + h, x,     y,     r);
        ctx.arcTo(x,     y,     x + w, y,     r);
        ctx.closePath();
    }

    // font contoh: '700 30px Georgia, serif' -> gaya='700 ', ukuran=30, fam='Georgia, serif'
    function teks(ctx, str, x, y, font, warna, maxLebar, spasi) {
        const m    = font.match(/^(.*?)(\d+)px\s+(.*)$/);
        const gaya = m[1];
        const fam  = m[3];
        let ukuran = parseInt(m[2], 10);

        ctx.letterSpacing = spasi || '0px';
        ctx.font = gaya + ukuran + 'px ' + fam;

        while (maxLebar && ctx.measureText(str).width > maxLebar && ukuran > 14) {
            ukuran -= 2;
            ctx.font = gaya + ukuran + 'px ' + fam;
        }

        ctx.fillStyle = warna;
        ctx.fillText(str, x, y);
    }

    async function gambarKartu() {
        const W = 1000, H = 630;
        const c = document.createElement('canvas');
        c.width = W; c.height = H;
        const ctx = c.getContext('2d');

        const [logo, foto] = await Promise.all([muatGambar(D.logo), muatGambar(D.foto)]);

        // Latar
        rr(ctx, 0, 0, W, H, 40);
        ctx.save(); ctx.clip();
        const g = ctx.createLinearGradient(0, 0, W, H);
        g.addColorStop(0, '#4A0012'); g.addColorStop(0.55, '#6B1124'); g.addColorStop(1, '#8B1A2E');
        ctx.fillStyle = g; ctx.fillRect(0, 0, W, H);

        // Lingkaran dekoratif
        ctx.strokeStyle = 'rgba(255,255,255,0.10)'; ctx.lineWidth = 4;
        [[W - 20, 20, 240], [W - 20, 20, 180], [40, H - 20, 160]].forEach(function (o) {
            ctx.beginPath(); ctx.arc(o[0], o[1], o[2], 0, Math.PI * 2); ctx.stroke();
        });
        ctx.restore();

        ctx.textBaseline = 'alphabetic';

        // Header
        if (logo) ctx.drawImage(logo, 50, 45, 100, 100);
        teks(ctx, 'HIMA INFORMATIKA', 175, 90, '700 30px Georgia, serif', '#ffffff', 460, '2px');
        teks(ctx, 'Universitas La Tansa Mashiro', 175, 128, '400 23px Arial, sans-serif', 'rgba(255,255,255,0.65)', 460);

        // Badge jabatan (otomatis mengecil jika teks panjang)
        rr(ctx, W - 250, 62, 200, 52, 26);
        ctx.fillStyle = 'rgba(255,255,255,0.15)'; ctx.fill();
        ctx.textAlign = 'center';
        teks(ctx, D.jabatan.toUpperCase(), W - 150, 97, '600 23px Arial, sans-serif', '#ffffff', 170, '2px');
        ctx.textAlign = 'left';

        // Garis
        ctx.fillStyle = 'rgba(255,255,255,0.2)'; ctx.fillRect(50, 172, W - 100, 2);

        // Foto
        const fx = 50, fy = 215, fs = 210;
        rr(ctx, fx, fy, fs, fs, 28);
        ctx.fillStyle = 'rgba(255,255,255,0.10)'; ctx.fill();
        if (foto) {
            ctx.save(); rr(ctx, fx, fy, fs, fs, 28); ctx.clip();
            const s = Math.max(fs / foto.width, fs / foto.height);
            const dw = foto.width * s, dh = foto.height * s;
            ctx.drawImage(foto, fx + (fs - dw) / 2, fy + (fs - dh) / 2, dw, dh);
            ctx.restore();
        }
        rr(ctx, fx, fy, fs, fs, 28);
        ctx.strokeStyle = 'rgba(255,255,255,0.35)'; ctx.lineWidth = 4; ctx.stroke();

        // Data
        const tx = 295, maxT = W - tx - 50;
        teks(ctx, D.nama,     tx, 275, '700 52px Georgia, serif', '#ffffff', maxT);
        teks(ctx, D.nim,      tx, 332, '400 36px "Courier New", monospace', 'rgba(255,255,255,0.85)', maxT, '2px');
        teks(ctx, D.prodi,    tx, 382, '400 30px Arial, sans-serif', 'rgba(255,255,255,0.75)', maxT);
        teks(ctx, D.angkatan, tx, 422, '400 27px Arial, sans-serif', 'rgba(255,255,255,0.65)', maxT);

        // Footer
        ctx.fillStyle = 'rgba(255,255,255,0.2)'; ctx.fillRect(50, 520, W - 100, 2);
        teks(ctx, 'KARTU TANDA ANGGOTA', 50, 576, '400 21px Arial, sans-serif', 'rgba(255,255,255,0.55)', 0, '4px');

        const label = D.aktif ? 'Aktif' : 'Tidak Aktif';
        rr(ctx, W - 230, 543, 180, 48, 24);
        ctx.fillStyle = D.aktif ? 'rgba(52,211,153,0.22)' : 'rgba(248,113,113,0.22)'; ctx.fill();
        ctx.textAlign = 'center';
        teks(ctx, label, W - 140, 576, '600 24px Arial, sans-serif', D.aktif ? '#6ee7b7' : '#fca5a5');
        ctx.textAlign = 'left';

        return c;
    }

    const btn   = document.getElementById('btn-unduh-png');
    const label = document.getElementById('btn-unduh-label');

    btn.addEventListener('click', async function () {
        btn.disabled = true;
        label.textContent = 'Memproses…';
        try {
            const canvas = await gambarKartu();
            canvas.toBlob(function (blob) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'KTA-HIMA-' + D.nim + '.png';
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(function () { URL.revokeObjectURL(url); }, 2000);
                label.textContent = 'Unduh KTA (PNG)';
                btn.disabled = false;
            }, 'image/png');
        } catch (e) {
            console.error(e);
            label.textContent = 'Gagal. Coba lagi.';
            btn.disabled = false;
        }
    });
})();
</script>
@endpush