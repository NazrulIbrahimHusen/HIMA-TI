<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $request->validate([
    'name'     => ['required', 'string', 'max:255'],
    'email'    => ['required', 'email', 'unique:users,email'],
    'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
   ], [
    'name.required'      => 'Nama lengkap wajib diisi.',
    'email.required'     => 'Email wajib diisi.',
    'email.email'        => 'Format email tidak valid.',
    'email.unique'       => 'Email ini sudah terdaftar.',
    'password.required'  => 'Password wajib diisi.',
    'password.confirmed' => 'Konfirmasi password tidak cocok.',
    'password.min'       => 'Password minimal 8 karakter.',
    'password.mixed'     => 'Password harus mengandung huruf besar dan huruf kecil.',
    'password.numbers'   => 'Password harus mengandung setidaknya satu angka.',
  ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun ini tidak memiliki akses admin.']);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
