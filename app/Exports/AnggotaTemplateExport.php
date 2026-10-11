<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AnggotaTemplateExport implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize
{
    public function headings(): array
    {
        return ['nim', 'nama', 'prodi', 'angkatan', 'semester'];
    }

    public function array(): array
    {
        return [
            ['525241018', 'Contoh Nama Satu', 'Informatika', '2024', '3'],
            ['525241019', 'Contoh Nama Dua',  'Informatika', '2024', '3'],
        ];
    }

    public function columnFormats(): array
    {
        // Kolom NIM sebagai teks supaya Excel tidak mengubah angkanya
        return ['A' => NumberFormat::FORMAT_TEXT];
    }
}
