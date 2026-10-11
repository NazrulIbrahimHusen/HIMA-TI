<?php

namespace App\Imports;

use App\Models\Anggota;
use App\Services\AnggotaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AnggotaImport implements ToCollection, WithHeadingRow
{
    public const KOLOM = ['nim', 'nama', 'prodi', 'angkatan', 'semester'];

    public int $dibuat     = 0;
    public int $dipulihkan = 0;
    public int $dilewati   = 0;
    /** @var array<int, array{baris:int, nim:string, pesan:string}> */
    public array $gagal    = [];
    public bool $formatSalah = false;

    public function __construct(private AnggotaService $service)
    {
    }

    public function collection(Collection $rows): void
    {
        $first = $rows->first();

        if (!$first || collect(self::KOLOM)->contains(fn ($k) => !$first->has($k))) {
            $this->formatSalah = true;
            return;
        }

        foreach ($rows->values() as $i => $row) {
            $baris = $i + 2; // baris 1 = header

            // Lewati baris kosong
            if ($row->filter(fn ($v) => $v !== null && $v !== '')->isEmpty()) {
                continue;
            }

            $data = [
                'nim'      => $this->teks($row['nim']),
                'nama'     => $this->teks($row['nama']),
                'prodi'    => $this->teks($row['prodi']),
                'angkatan' => $this->teks($row['angkatan']),
                'semester' => $this->teks($row['semester']),
                'status'   => 'aktif',
            ];

            $validator = Validator::make($data, [
                'nim'      => ['required', 'digits_between:1,10'],
                'nama'     => ['required', 'max:255'],
                'prodi'    => ['required', 'max:100'],
                'angkatan' => ['required', 'integer', 'between:2000,' . (date('Y') + 1)],
                'semester' => ['required', 'integer', 'between:1,14'],
            ], [
                'required'       => ':attribute wajib diisi.',
                'digits_between' => ':attribute harus berupa angka, maksimal 10 digit.',
                'integer'        => ':attribute harus berupa angka.',
                'between'        => ':attribute di luar rentang yang diizinkan.',
                'max'            => ':attribute terlalu panjang.',
            ], [
                'nim' => 'NIM', 'nama' => 'Nama', 'prodi' => 'Prodi',
                'angkatan' => 'Angkatan', 'semester' => 'Semester',
            ]);

            if ($validator->fails()) {
                $this->gagal[] = ['baris' => $baris, 'nim' => $data['nim'], 'pesan' => $validator->errors()->first()];
                continue;
            }

            // NIM sudah terdaftar & aktif → lewati
            if (Anggota::where('nim', $data['nim'])->exists()) {
                $this->dilewati++;
                continue;
            }

            try {
                [, $hasil] = $this->service->simpan($data);
                $hasil === AnggotaService::HASIL_DIPULIHKAN ? $this->dipulihkan++ : $this->dibuat++;
            } catch (\Throwable $e) {
                $this->gagal[] = ['baris' => $baris, 'nim' => $data['nim'], 'pesan' => 'Gagal menyimpan: ' . $e->getMessage()];
            }
        }
    }

    /** Excel kadang membaca angka sebagai float (mis. 5.25E+8) — ubah jadi teks bersih. */
    private function teks(mixed $nilai): string
    {
        if (is_int($nilai) || is_float($nilai)) {
            return number_format((float) $nilai, 0, '', '');
        }

        return trim((string) $nilai);
    }
}
