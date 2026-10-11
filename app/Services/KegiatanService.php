<?php

namespace App\Services;

use App\Models\Kegiatan;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KegiatanService
{
    /** Buat kegiatan (status draft) beserta sesi absensinya. Token UUID dibuat otomatis oleh model. */
    public function simpan(array $data, array $sesi): Kegiatan
    {
        return DB::transaction(function () use ($data, $sesi) {
            $kegiatan = new Kegiatan();
            $kegiatan->fill($this->atribut($data, $sesi) + ['status' => 'draft']);
            $kegiatan->save();

            foreach ($sesi as $s) {
                $kegiatan->sesiAbsensi()->create($this->atributSesi($s));
            }

            return $kegiatan;
        });
    }

    /** Ubah kegiatan dan sinkronkan sesi. Sesi yang sudah punya absensi tidak boleh dihapus. */
    public function perbarui(Kegiatan $kegiatan, array $data, array $sesi): Kegiatan
    {
        return DB::transaction(function () use ($kegiatan, $data, $sesi) {
            $kegiatan->update($this->atribut($data, $sesi));

            $idDipertahankan = collect($sesi)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

            $akanDihapus = $kegiatan->sesiAbsensi()->whereNotIn('id', $idDipertahankan)->get();
            foreach ($akanDihapus as $s) {
                if ($s->absensi()->withTrashed()->exists()) {
                    throw new DomainException("Sesi \"{$s->nama_sesi}\" tidak bisa dihapus karena sudah memiliki data absensi.");
                }
            }
            foreach ($akanDihapus as $s) {
                $s->delete();
            }

            foreach ($sesi as $s) {
                $atribut = $this->atributSesi($s);
                if (!empty($s['id'])) {
                    $kegiatan->sesiAbsensi()->where('id', $s['id'])->update($atribut);
                } else {
                    $kegiatan->sesiAbsensi()->create($atribut);
                }
            }

            return $kegiatan;
        });
    }

    public function buatTokenBaru(Kegiatan $kegiatan): void
    {
        $kegiatan->update(['token' => (string) Str::uuid()]);
    }

    /** Tanggal kegiatan diturunkan dari sesi paling awal dan paling akhir. */
    private function atribut(array $data, array $sesi): array
    {
        $mulai   = collect($sesi)->map(fn ($s) => Carbon::parse($s['waktu_mulai']))->min();
        $selesai = collect($sesi)->map(fn ($s) => Carbon::parse($s['waktu_selesai']))->max();

        return [
            'nama'            => $data['nama'],
            'tipe'            => $data['tipe'],
            'lokasi'          => $data['lokasi'] ?? null,
            'deskripsi'       => $data['deskripsi'] ?? null,
            'tanggal_mulai'   => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
        ];
    }

    private function atributSesi(array $s): array
    {
        return [
            'nama_sesi'     => $s['nama_sesi'],
            'waktu_mulai'   => Carbon::parse($s['waktu_mulai'])->format('Y-m-d H:i:s'),
            'waktu_selesai' => Carbon::parse($s['waktu_selesai'])->format('Y-m-d H:i:s'),
        ];
    }
}
