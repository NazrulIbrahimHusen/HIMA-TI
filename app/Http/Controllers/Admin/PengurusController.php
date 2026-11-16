<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Divisi;
use App\Models\PeriodeKepengurusan;
use App\Models\Pengurus;
use Illuminate\Http\Request;

class PengurusController extends Controller
{
    public function index(Request $request)
    {
        $daftarPeriode = PeriodeKepengurusan::orderByDesc('tahun_mulai')->orderByDesc('id')->get();

        $periode = $daftarPeriode->firstWhere('id', (int) $request->query('periode'))
            ?? $daftarPeriode->firstWhere('status', 'aktif')
            ?? $daftarPeriode->first();

        $divisi = collect();
        $inti   = collect();

        if ($periode) {
            $divisi = Divisi::where('periode_id', $periode->id)
                ->with(['pengurus' => fn ($q) => $q->with('anggota')->orderBy('urutan')->orderBy('id')])
                ->orderBy('urutan')->orderBy('id')
                ->get();

            $inti = Pengurus::with('anggota')
                ->where('periode_id', $periode->id)
                ->whereNull('divisi_id')
                ->orderBy('urutan')->orderBy('id')
                ->get();
        }

        return view('admin.pengurus.index', compact('daftarPeriode', 'periode', 'divisi', 'inti'));
    }

    public function create(Request $request)
    {
        $periode = PeriodeKepengurusan::find((int) $request->query('periode'))
            ?? PeriodeKepengurusan::where('status', 'aktif')->first();

        if (!$periode) {
            return redirect()->route('admin.periode.index')
                ->with('error', 'Belum ada periode aktif. Buat atau aktifkan periode terlebih dahulu.');
        }

        $sudahPengurus = Pengurus::where('periode_id', $periode->id)->pluck('anggota_id');

        $calonList = Anggota::aktif()->whereNotIn('id', $sudahPengurus)->orderBy('nama')->get(['nim', 'nama']);

        // NIM terisi otomatis (dari tombol "Jadikan pengurus"), hanya jika memang masih bisa dipilih
        $nim     = (string) $request->query('nim');
        $nimAwal = $nim !== '' && $calonList->contains('nim', $nim) ? $nim : null;

        return view('admin.pengurus.form', [
            'periode'    => $periode,
            'pengurus'   => null,
            'divisiList' => Divisi::where('periode_id', $periode->id)->orderBy('urutan')->orderBy('id')->get(),
            'calonList'  => $calonList,
            'divisiAwal' => $request->query('divisi'),
            'nimAwal'    => $nimAwal,
        ]);
    }

    public function store(Request $request)
    {
        $periode = PeriodeKepengurusan::findOrFail((int) $request->input('periode_id'));

        $data = $request->validate([
            'nim'       => ['required', 'digits_between:1,10'],
            'jabatan'   => ['required', 'string', 'max:100'],
            'divisi_id' => ['nullable', 'integer'],
            'urutan'    => ['nullable', 'integer', 'between:0,127'],
        ], $this->pesan());

        $anggota = Anggota::where('nim', $data['nim'])->first();
        if (!$anggota) {
            return back()->withInput()->withErrors(['nim' => 'NIM tidak ditemukan di data anggota.']);
        }

        if (Pengurus::where('periode_id', $periode->id)->where('anggota_id', $anggota->id)->exists()) {
            return back()->withInput()->withErrors(['nim' => "{$anggota->nama} sudah tercatat sebagai pengurus pada periode {$periode->nama}."]);
        }

        $divisiId = $this->divisiValid($data['divisi_id'] ?? null, $periode);
        if ($divisiId === false) {
            return back()->withInput()->withErrors(['divisi_id' => 'Divisi tidak valid untuk periode ini.']);
        }

        Pengurus::create([
            'anggota_id' => $anggota->id,
            'periode_id' => $periode->id,
            'divisi_id'  => $divisiId,
            'jabatan'    => $data['jabatan'],
            'urutan'     => $data['urutan'] ?? $this->urutanBerikut($periode->id, $divisiId),
        ]);

        return redirect()->route('admin.pengurus.index', ['periode' => $periode->id])
            ->with('success', "{$anggota->nama} ditambahkan sebagai {$data['jabatan']}.");
    }

    public function edit(Pengurus $pengurus)
    {
        $pengurus->load('anggota', 'periode');

        return view('admin.pengurus.form', [
            'periode'    => $pengurus->periode,
            'pengurus'   => $pengurus,
            'divisiList' => Divisi::where('periode_id', $pengurus->periode_id)->orderBy('urutan')->orderBy('id')->get(),
            'calonList'  => collect(),
            'divisiAwal' => null,
            'nimAwal'    => null,
        ]);
    }

    public function update(Request $request, Pengurus $pengurus)
    {
        $data = $request->validate([
            'jabatan'   => ['required', 'string', 'max:100'],
            'divisi_id' => ['nullable', 'integer'],
            'urutan'    => ['required', 'integer', 'between:0,127'],
        ], $this->pesan());

        $divisiId = $this->divisiValid($data['divisi_id'] ?? null, $pengurus->periode);
        if ($divisiId === false) {
            return back()->withInput()->withErrors(['divisi_id' => 'Divisi tidak valid untuk periode ini.']);
        }

        $pengurus->update([
            'jabatan'   => $data['jabatan'],
            'divisi_id' => $divisiId,
            'urutan'    => $data['urutan'],
        ]);

        return redirect()->route('admin.pengurus.index', ['periode' => $pengurus->periode_id])
            ->with('success', 'Data pengurus berhasil diperbarui.');
    }

    public function destroy(Pengurus $pengurus)
    {
        $nama = $pengurus->anggota?->nama ?? 'Pengurus';
        $pengurus->delete();

        return redirect()->route('admin.pengurus.index', ['periode' => $pengurus->periode_id])
            ->with('success', "{$nama} dihapus dari struktur pengurus.");
    }

    /** @return int|null|false null = tanpa divisi, false = tidak valid */
    private function divisiValid(mixed $divisiId, PeriodeKepengurusan $periode): int|null|false
    {
        if (!$divisiId) {
            return null;
        }

        return Divisi::where('id', (int) $divisiId)->where('periode_id', $periode->id)->exists() ? (int) $divisiId : false;
    }

    private function urutanBerikut(int $periodeId, ?int $divisiId): int
    {
        return (int) Pengurus::where('periode_id', $periodeId)->where('divisi_id', $divisiId)->max('urutan') + 1;
    }

    private function pesan(): array
    {
        return [
            'nim.required'       => 'NIM anggota wajib diisi.',
            'nim.digits_between' => 'NIM harus berupa angka, maksimal 10 digit.',
            'jabatan.required'   => 'Jabatan wajib diisi.',
            'urutan.integer'     => 'Urutan harus berupa angka.',
            'urutan.between'     => 'Urutan harus antara 0 sampai 127.',
        ];
    }
}