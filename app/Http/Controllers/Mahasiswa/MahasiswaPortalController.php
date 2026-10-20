<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Services\MonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MahasiswaPortalController extends Controller
{
    public function __construct(private MonitoringService $monitoring) {}

    /* ────────────────── DASHBOARD ────────────────── */

    public function dashboard()
    {
        $user    = auth()->user();
        $anggota = $user->anggota;

        // Data monitoring ringkas (semester terakhir)
        $rentang = $this->monitoring->rentang('semester');
        $riwayat = $anggota
            ? $this->monitoring->riwayat($anggota, $rentang['dari'], $rentang['sampai'])
            : ['hadir' => 0, 'total' => 0, 'persen' => null, 'grade' => null, 'baris' => collect()];

        // Kegiatan mendatang (published, belum selesai)
        $mendatang = Kegiatan::published()
            ->where('tanggal_mulai', '>=', now()->toDateString())
            ->orderBy('tanggal_mulai')
            ->limit(3)
            ->get();

        // Kegiatan terbaru yang sudah punya absensi si mahasiswa ini
        $terakhir = $riwayat['baris']->take(3);

        return view('mahasiswa.dashboard', compact('user', 'anggota', 'riwayat', 'mendatang', 'terakhir'));
    }

    /* ────────────────── ABSENSI ────────────────── */

    public function absensi(Request $request)
    {
        $user    = auth()->user();
        $anggota = $user->anggota;

        if (!$anggota) {
            return view('mahasiswa.absensi', ['riwayat' => collect(), 'ringkasan' => null]);
        }

        $rentang = $this->monitoring->rentang($request->get('rentang', 'semester'));
        $riwayat = $this->monitoring->riwayat($anggota, $rentang['dari'], $rentang['sampai']);

        // Filter status jika ada
        $baris = collect($riwayat['baris']);
        if ($status = $request->get('status')) {
            $baris = $baris->filter(fn ($b) => $b['hadir'] === ($status === 'hadir'));
        }

        return view('mahasiswa.absensi', [
            'baris'       => $baris->values(),
            'ringkasan'   => $riwayat,
            'rentang'     => $rentang,
            'opsiRentang' => $this->monitoring->opsiRentang(),
        ]);
    }

    /* ────────────────── MONITORING ────────────────── */

    public function monitoring(Request $request)
    {
        $user    = auth()->user();
        $anggota = $user->anggota;

        if (!$anggota) {
            return view('mahasiswa.monitoring', ['riwayat' => null]);
        }

        $rentang = $this->monitoring->rentang($request->get('rentang', 'semester'));
        $riwayat = $this->monitoring->riwayat($anggota, $rentang['dari'], $rentang['sampai']);

        // Distribusi per bulan untuk grafik (hanya kegiatan yg punya tanggal)
        $perBulan = collect($riwayat['baris'])
            ->groupBy(fn ($b) => $b['kegiatan']->tanggal_mulai->format('Y-m'))
            ->map(fn ($grup) => [
                'label'  => $grup->first()['kegiatan']->tanggal_mulai->locale('id')->translatedFormat('M Y'),
                'hadir'  => $grup->where('hadir', true)->count(),
                'total'  => $grup->count(),
            ])
            ->sortKeys()
            ->values();

        return view('mahasiswa.monitoring', [
            'anggota'     => $anggota,
            'riwayat'     => $riwayat,
            'perBulan'    => $perBulan,
            'rentang'     => $rentang,
            'opsiRentang' => $this->monitoring->opsiRentang(),
            'grade'       => MonitoringService::GRADE,
        ]);
    }

    /* ────────────────── KTA DIGITAL ────────────────── */

    public function kta()
    {
        $user    = auth()->user();
        $anggota = $user->anggota;

        return view('mahasiswa.kta', compact('user', 'anggota'));
    }

    /* ────────────────── PROFIL ────────────────── */

    public function profil()
    {
        $user    = auth()->user();
        $anggota = $user->anggota;

        return view('mahasiswa.profil', compact('user', 'anggota'));
    }

    public function updateFoto(Request $request)
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'foto.required' => 'Pilih file foto terlebih dahulu.',
            'foto.image'    => 'File harus berupa gambar.',
            'foto.mimes'    => 'Format foto: JPG, PNG, atau WebP.',
            'foto.max'      => 'Ukuran foto maksimal 2 MB.',
        ]);

        $anggota = auth()->user()->anggota;

        abort_if(!$anggota, 403, 'Data anggota tidak ditemukan.');

        // Hapus foto lama jika ada
        if ($anggota->foto) {
            Storage::disk('public')->delete($anggota->foto);
        }

        $path = $request->file('foto')->store('foto-anggota', 'public');
        $anggota->update(['foto' => $path]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama'  => ['required'],
            'password'       => ['required', 'min:8', 'confirmed'],
        ], [
            'password_lama.required'  => 'Password lama wajib diisi.',
            'password.required'       => 'Password baru wajib diisi.',
            'password.min'            => 'Password minimal 8 karakter.',
            'password.confirmed'      => 'Konfirmasi password tidak cocok.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $user->update([
            'password'         => Hash::make($request->password),
            'password_changed' => true,
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
