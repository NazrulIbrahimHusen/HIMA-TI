<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\Pengumuman;
use App\Models\Pengurus;

class BerandaController extends Controller
{
    public function index()
    {
        return view('publik.beranda', [
            'jumlahAnggota'  => Anggota::aktif()->count(),
            'jumlahKegiatan' => Kegiatan::publik()->count(),
            'jumlahPengurus' => Pengurus::periodeAktif()->count(),
            'kegiatan'       => Kegiatan::publik()->orderByDesc('tanggal_mulai')->orderByDesc('id')->limit(3)->get(),
            'pengumuman'     => Pengumuman::published()->orderByDesc('published_at')->limit(3)->get(),
        ]);
    }
}
