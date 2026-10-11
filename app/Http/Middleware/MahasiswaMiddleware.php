<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('mahasiswa.login');
        }

        if (!Auth::user()->isMahasiswa()) {
            Auth::logout();
            return redirect()->route('mahasiswa.login')
                             ->withErrors(['nim' => 'Akun ini bukan akun mahasiswa.']);
        }

        if (!Auth::user()->password_changed &&
            !$request->routeIs('mahasiswa.password.*')) {
            return redirect()->route('mahasiswa.password.change');
        }

        return $next($request);
    }
}
