<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isMahasiswa()) {
            return redirect()->route('mahasiswa.dashboard');
        }
        return view('auth.mahasiswa-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nim'      => 'required|numeric|max_digits:10',
            'password' => 'required',
        ], [
            'nim.required'      => 'NIM wajib diisi.',
            'nim.numeric'       => 'NIM harus berupa angka.',
            'nim.max_digits'    => 'NIM maksimal 10 digit.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = [
            'nim'      => $request->nim,
            'password' => $request->password,
        ];
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->isMahasiswa()) {
                Auth::logout();
                return back()->withErrors(['akun' => 'Akun ini bukan akun mahasiswa.']);
            }

            // Hanya anggota berstatus aktif yang boleh masuk portal.
            // Anggota yang dihapus (soft delete) otomatis ikut ditolak karena relasinya null.
            if ($user->anggota?->status !== 'aktif') {
                Auth::logout();
                return back()
                    ->withErrors(['akun' => 'Akun Anda sudah tidak aktif. Hubungi pengurus HIMA untuk informasi lebih lanjut.'])
                    ->withInput($request->only('nim'));
            }

            $request->session()->regenerate();

            if (!$user->password_changed) {
                return redirect()->route('mahasiswa.password.change');
            }

            return redirect()->intended(route('mahasiswa.dashboard'));
        }

        return back()->withErrors(['akun' => 'NIM atau password salah.'])->withInput($request->only('nim'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('mahasiswa.login');
    }

    public function showChangePasswordForm()
    {
        if (Auth::user()->password_changed) {
            return redirect()->route('mahasiswa.dashboard');
        }
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        Auth::user()->update([
            'password'         => bcrypt($request->password),
            'password_changed' => true,
        ]);

        return redirect()->route('mahasiswa.dashboard')
                         ->with('success', 'Password berhasil diubah. Selamat datang!');
    }
}