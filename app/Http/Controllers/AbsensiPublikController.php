<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Kegiatan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AbsensiPublikController extends Controller
{
    public function form(Request $request, string $token)
    {
        $kegiatan = $this->cariKegiatan($token);
        [$state, $sesiAktif, $sesiBerikut] = $this->tentukanSesi($kegiatan);

        return view('absen.form', [
            'kegiatan'    => $kegiatan,
            'state'       => $state,
            'sesiAktif'   => $sesiAktif,
            'sesiBerikut' => $sesiBerikut,
            'via'         => $request->query('via') === 'qr' ? 'qr' : 'link',
            'siteKey'     => config('recaptcha.site_key'),
            'butuhNama'   => session('butuh_nama') || old('nama') !== null,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $kegiatan = $this->cariKegiatan($token);

        $data = $request->validate([
            'nim'  => ['required', 'digits_between:1,10'],
            'nama' => ['nullable', 'string', 'max:255'],
            'via'  => ['nullable', 'in:qr,link'],
        ], [
            'nim.required'       => 'NIM wajib diisi.',
            'nim.digits_between' => 'NIM harus berupa angka, maksimal 10 digit.',
        ]);

        if ($pesanCaptcha = $this->periksaCaptcha($request)) {
            return back()->withInput()->withErrors(['captcha' => $pesanCaptcha]);
        }

        // Time-gate diperiksa ulang di server saat submit
        [$state, $sesi] = $this->tentukanSesi($kegiatan);
        if ($state !== 'aktif') {
            return redirect()->route('absen.form', $token)->withErrors(['nim' => 'Absensi sedang tidak dibuka.']);
        }

        $anggota = Anggota::where('nim', $data['nim'])->first();
        $nama    = $anggota?->nama ?? trim((string) ($data['nama'] ?? ''));

        // NIM tidak dikenal → minta nama dulu untuk absen sebagai tamu
        if (!$anggota && $nama === '') {
            return back()->withInput($request->only('nim', 'via'))
                ->with('butuh_nama', true)
                ->with('info', 'NIM belum terdaftar sebagai anggota HIMA. Isi nama lengkap untuk absen sebagai tamu.');
        }

        $existing = Absensi::withTrashed()->where('sesi_id', $sesi->id)->where('nim', $data['nim'])->first();
        // Sudah tercatat hadir/izin/sakit → tolak. Status "alpa" (otomatis) boleh diperbarui jadi hadir.
        if ($existing && !$existing->trashed() && $existing->status !== 'alpa') {
            return redirect()->route('absen.form', $token)->with('sudah', [
                'nama'   => $existing->nama,
                'sesi'   => $sesi->nama_sesi,
                'status' => $existing->status,
            ]);
        }

        $atribut = [
            'sesi_id'      => $sesi->id,
            'anggota_id'   => $anggota?->id,
            'nim'          => $data['nim'],
            'nama'         => $nama,
            'tipe_peserta' => $anggota ? 'anggota' : 'tamu',
            'status'       => 'hadir',
            'metode'       => ($data['via'] ?? 'link') === 'qr' ? 'qr' : 'link',
            'waktu_absen'  => now(),
        ];

        try {
            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update($atribut);
            } else {
                Absensi::create($atribut);
            }
        } catch (UniqueConstraintViolationException) {
            // Terkirim dua kali bersamaan → anggap sudah absen
            return redirect()->route('absen.form', $token)->with('sudah', ['nama' => $nama, 'sesi' => $sesi->nama_sesi, 'status' => 'hadir']);
        }

        return redirect()->route('absen.form', $token)->with('berhasil', [
            'nama'  => $nama,
            'nim'   => $data['nim'],
            'tamu'  => !$anggota,
            'sesi'  => $sesi->nama_sesi,
            'waktu' => now()->format('H:i'),
        ]);
    }

    private function cariKegiatan(string $token): Kegiatan
    {
        $kegiatan = Kegiatan::where('token', $token)->firstOrFail();

        // Kegiatan draft belum boleh diakses publik
        abort_if($kegiatan->status === 'draft', 404);

        return $kegiatan;
    }

    /** @return array{0:string, 1:?\App\Models\SesiAbsensi, 2:?\App\Models\SesiAbsensi} */
    private function tentukanSesi(Kegiatan $kegiatan): array
    {
        if ($kegiatan->status === 'closed') {
            return ['tutup', null, null];
        }

        $sekarang = now();

        $aktif = $kegiatan->sesiAbsensi()
            ->where('waktu_mulai', '<=', $sekarang)
            ->where('waktu_selesai', '>=', $sekarang)
            ->orderBy('waktu_mulai')
            ->first();

        if ($aktif) {
            return ['aktif', $aktif, null];
        }

        $berikut = $kegiatan->sesiAbsensi()->where('waktu_mulai', '>', $sekarang)->orderBy('waktu_mulai')->first();

        return $berikut ? ['belum', null, $berikut] : ['selesai', null, null];
    }

    /** Kembalikan null jika lolos, atau pesan kesalahan yang jelas jika gagal. */
    private function periksaCaptcha(Request $request): ?string
    {
        $secret = config('recaptcha.secret_key');

        // Belum dikonfigurasi (development) → lewati
        if (!$secret) {
            return null;
        }

        $respon = $request->input('g-recaptcha-response');
        if (!$respon) {
            return 'Centang kotak "Saya bukan robot" terlebih dahulu.';
        }

        try {
            $hasil = Http::asForm()->timeout(8)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $respon,
                'remoteip' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA: gagal menghubungi Google: ' . $e->getMessage());

            return 'Server tidak dapat menghubungi layanan reCAPTCHA. Coba lagi sebentar atau hubungi admin.';
        }

        if (!$hasil->json('success')) {
            Log::warning('reCAPTCHA ditolak Google', ['kode' => $hasil->json('error-codes')]);

            return 'Verifikasi reCAPTCHA gagal. Muat ulang halaman lalu coba lagi.';
        }

        return null;
    }
}
