<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class KelolaAdminController extends Controller
{
    /**
     * Daftar semua akun admin (bukan super_admin, bukan mahasiswa).
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'admin')->withTrashed();

        if ($cari = $request->get('cari')) {
            $query->where(function ($q) use ($cari) {
                $q->where('name', 'like', "%{$cari}%")
                  ->orWhere('email', 'like', "%{$cari}%");
            });
        }

        if ($request->get('status') === 'nonaktif') {
            $query->onlyTrashed();
        } elseif ($request->get('status') !== 'semua') {
            $query->whereNull('deleted_at');
        }

        $admins = $query->latest()->paginate(25)->withQueryString();

        return view('admin.kelola-admin.index', compact('admins'));
    }

    /**
     * Form tambah akun admin baru.
     */
    public function create()
    {
        return view('admin.kelola-admin.form', ['admin' => null]);
    }

    /**
     * Simpan akun admin baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah dipakai akun lain.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'nim'              => null,
            'password'         => Hash::make($request->password),
            'role'             => 'admin',
            'password_changed' => true,
        ]);

        return redirect()->route('admin.super.akun')
            ->with('success', "Akun admin \"{$request->name}\" berhasil ditambahkan.");
    }

    /**
     * Form edit akun admin.
     */
    public function edit(User $kelola_admin)
    {
        abort_if($kelola_admin->role !== 'admin', 404);
        return view('admin.kelola-admin.form', ['admin' => $kelola_admin]);
    }

    /**
     * Perbarui data akun admin.
     */
    public function update(Request $request, User $kelola_admin)
    {
        abort_if($kelola_admin->role !== 'admin', 404);

        $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', "unique:users,email,{$kelola_admin->id}"],
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah dipakai akun lain.',
        ]);

        $kelola_admin->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('admin.super.akun')
            ->with('success', "Data admin \"{$kelola_admin->name}\" berhasil diperbarui.");
    }

    /**
     * Reset password akun admin.
     */
    public function resetPassword(Request $request, User $kelola_admin)
    {
        abort_if($kelola_admin->role !== 'admin', 404);

        $request->validate([
            'password' => ['required', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $kelola_admin->update([
            'password'         => Hash::make($request->password),
            'password_changed' => false, // wajib ganti saat login berikutnya
        ]);

        return redirect()->route('admin.super.akun')
            ->with('success', "Password admin \"{$kelola_admin->name}\" berhasil direset. Admin wajib ganti password saat login.");
    }

    /**
     * Nonaktifkan (soft delete) akun admin.
     */
    public function destroy(User $kelola_admin)
    {
        abort_if($kelola_admin->role !== 'admin', 404);

        $kelola_admin->delete();

        return redirect()->route('admin.super.akun')
            ->with('success', "Akun admin \"{$kelola_admin->name}\" telah dinonaktifkan.");
    }

    /**
     * Aktifkan kembali akun admin yang sudah dinonaktifkan.
     */
    public function restore(int $id)
    {
        $kelola_admin = User::onlyTrashed()->where('role', 'admin')->findOrFail($id);
        $kelola_admin->restore();

        return redirect()->route('admin.super.akun')
            ->with('success', "Akun admin \"{$kelola_admin->name}\" berhasil diaktifkan kembali.");
    }
}
