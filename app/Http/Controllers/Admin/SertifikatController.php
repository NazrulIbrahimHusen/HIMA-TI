<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\Request;

class SertifikatController extends Controller
{
    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'sertifikat_url' => ['nullable', 'url:https', 'max:2000'],
        ], [
            'sertifikat_url.url' => 'Link sertifikat harus berupa alamat https yang valid (mis. link Google Drive).',
            'sertifikat_url.max' => 'Link sertifikat terlalu panjang.',
        ]);

        $aktif = $request->boolean('sertifikat_aktif');

        if ($aktif && blank($data['sertifikat_url'] ?? null)) {
            return back()->withInput()->withErrors(['sertifikat_url' => 'Isi link sertifikat sebelum mengaktifkannya.']);
        }

        $kegiatan->update([
            'sertifikat_url'   => $data['sertifikat_url'] ?? null,
            'sertifikat_aktif' => $aktif,
        ]);

        return back()->with('success', $aktif
            ? 'Sertifikat diaktifkan. Peserta yang hadir kini bisa mengunduhnya.'
            : 'Pengaturan sertifikat disimpan (belum aktif untuk peserta).');
    }
}
