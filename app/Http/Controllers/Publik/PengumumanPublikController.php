<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;

class PengumumanPublikController extends Controller
{
    public function index()
    {
        $pengumuman = Pengumuman::published()->orderByDesc('published_at')->paginate(10);

        return view('publik.pengumuman.index', compact('pengumuman'));
    }

    public function show(Pengumuman $pengumuman)
    {
        abort_unless($pengumuman->isPublished(), 404);

        $lainnya = Pengumuman::published()
            ->where('id', '!=', $pengumuman->id)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('publik.pengumuman.show', compact('pengumuman', 'lainnya'));
    }
}
