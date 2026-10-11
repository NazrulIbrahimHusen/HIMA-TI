<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiPublikController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\AnggotaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KegiatanController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\MahasiswaAuthController;

// ── Halaman Publik ────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Absensi via link / QR (publik, dilindungi token UUID + time-gate + reCAPTCHA + rate limit)
Route::get('/absen/{token}', [AbsensiPublikController::class, 'form'])->name('absen.form');
Route::post('/absen/{token}', [AbsensiPublikController::class, 'store'])
    ->middleware('throttle:' . config('absensi.rate_limit', 5) . ',1')
    ->name('absen.store');

// ── Auth & Panel Admin ────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login',  [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    });

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Anggota (rute import harus sebelum resource)
        Route::get('anggota/import',          [AnggotaController::class, 'importForm'])->name('anggota.import');
        Route::post('anggota/import',         [AnggotaController::class, 'import'])->name('anggota.import.store');
        Route::get('anggota/import/template', [AnggotaController::class, 'template'])->name('anggota.import.template');
        Route::post('anggota/{anggota}/reset-password', [AnggotaController::class, 'resetPassword'])->name('anggota.reset-password');
        Route::resource('anggota', AnggotaController::class)->except(['show']);

        // Kegiatan + sesi absensi + token/QR
        Route::get('kegiatan/{kegiatan}/qr',       [KegiatanController::class, 'qr'])->name('kegiatan.qr');
        Route::post('kegiatan/{kegiatan}/token',   [KegiatanController::class, 'buatTokenBaru'])->name('kegiatan.token');
        Route::patch('kegiatan/{kegiatan}/status', [KegiatanController::class, 'ubahStatus'])->name('kegiatan.status');
        Route::resource('kegiatan', KegiatanController::class);

        // Absensi (rekap, catat manual, ubah status + log, export)
        Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('kegiatan/{kegiatan}/absensi',  [AbsensiController::class, 'kegiatan'])->name('absensi.kegiatan');
        Route::post('kegiatan/{kegiatan}/absensi', [AbsensiController::class, 'tambah'])->name('absensi.tambah');
        Route::post('kegiatan/{kegiatan}/absensi/isi-alpa', [AbsensiController::class, 'isiAlpa'])->name('absensi.isi-alpa');
        Route::get('kegiatan/{kegiatan}/absensi/export/excel', [AbsensiController::class, 'exportExcel'])->name('absensi.export.excel');
        Route::get('kegiatan/{kegiatan}/absensi/export/pdf',   [AbsensiController::class, 'exportPdf'])->name('absensi.export.pdf');
        Route::get('kegiatan/{kegiatan}/absensi/peserta/{nim}',    [AbsensiController::class, 'peserta'])->where('nim', '[0-9]+')->name('absensi.peserta');
        Route::put('kegiatan/{kegiatan}/absensi/peserta/{nim}',    [AbsensiController::class, 'perbaruiPeserta'])->where('nim', '[0-9]+')->name('absensi.peserta.update');
        Route::delete('kegiatan/{kegiatan}/absensi/peserta/{nim}', [AbsensiController::class, 'hapusPeserta'])->where('nim', '[0-9]+')->name('absensi.peserta.hapus');

        // Super Admin only
        Route::middleware('superadmin')->prefix('super')->name('super.')->group(function () {
            Route::get('/akun', function () {
                return 'Kelola akun admin — coming soon';
            })->name('akun');
        });
    });
});

// ── Auth & Portal Mahasiswa ───────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [MahasiswaAuthController::class, 'showLoginForm'])->name('mahasiswa.login');
    Route::post('/login', [MahasiswaAuthController::class, 'login'])->name('mahasiswa.login.post');
});

Route::post('/logout', [MahasiswaAuthController::class, 'logout'])->name('mahasiswa.logout');

Route::middleware('mahasiswa_auth')->prefix('portal')->name('mahasiswa.')->group(function () {
    Route::get('/ganti-password',  [MahasiswaAuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/ganti-password', [MahasiswaAuthController::class, 'changePassword'])->name('password.update');

    Route::get('/dashboard', function () {
        return view('mahasiswa.dashboard');
    })->name('dashboard');
});
