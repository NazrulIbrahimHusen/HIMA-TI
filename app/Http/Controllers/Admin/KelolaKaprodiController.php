<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class KelolaKaprodiController extends Controller
{
    /**
     * Daftar semua akun kaprodi.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'kaprodi')->withTrashed();

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

        $kaprodi_list = $query->latest()->paginate(25)->withQueryString();

        return view('admin.kaprodi.index', compact('kaprodi_list'));
    }

    /**
     * Form tambah akun kaprodi baru.
     */
    public function create()
    {
        return view('admin.kaprodi.form', ['kaprodi' => null]);
    }

    /**
     * Simpan akun kaprodi baru.
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
            'role'             => 'kaprodi',
            'password_changed' => true,
        ]);

        return redirect()->route('admin.super.kaprodi')
            ->with('success', "Akun Kaprodi \"{$request->name}\" berhasil ditambahkan.");
    }

    /**
     * Form edit akun kaprodi.
     */
    public function edit(User $kelola_kaprodi)
    {
        abort_if($kelola_kaprodi->role !== 'kaprodi', 404);
        return view('admin.kaprodi.form', ['kaprodi' => $kelola_kaprodi]);
    }

    /**
     * Perbarui data akun kaprodi.
     */
    public function update(Request $request, User $kelola_kaprodi)
    {
        abort_if($kelola_kaprodi->role !== 'kaprodi', 404);

        $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', "unique:users,email,{$kelola_kaprodi->id}"],
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah dipakai akun lain.',
        ]);

        $kelola_kaprodi->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('admin.super.kaprodi')
            ->with('success', "Data Kaprodi \"{$kelola_kaprodi->name}\" berhasil diperbarui.");
    }

    /**
     * Reset password akun kaprodi.
     */
    public function resetPassword(Request $request, User $kelola_kaprodi)
    {
        abort_if($kelola_kaprodi->role !== 'kaprodi', 404);

        $request->validate([
            'password' => ['required', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $kelola_kaprodi->update([
            'password'         => Hash::make($request->password),
            'password_changed' => false,
        ]);

        return redirect()->route('admin.super.kaprodi')
            ->with('success', "Password Kaprodi \"{$kelola_kaprodi->name}\" berhasil direset.");
    }

    /**
     * Nonaktifkan (soft delete) akun kaprodi.
     */
    public function destroy(User $kelola_kaprodi)
    {
        abort_if($kelola_kaprodi->role !== 'kaprodi', 404);

        $kelola_kaprodi->delete();

        return redirect()->route('admin.super.kaprodi')
            ->with('success', "Akun Kaprodi \"{$kelola_kaprodi->name}\" telah dinonaktifkan.");
    }

    /**
     * Aktifkan kembali akun kaprodi.
     */
    public function restore(int $id)
    {
        $kelola_kaprodi = User::onlyTrashed()->where('role', 'kaprodi')->findOrFail($id);
        $kelola_kaprodi->restore();

        return redirect()->route('admin.super.kaprodi')
            ->with('success', "Akun Kaprodi \"{$kelola_kaprodi->name}\" berhasil diaktifkan kembali.");
    }
}
