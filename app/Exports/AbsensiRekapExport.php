<?php

namespace App\Exports;

use App\Models\Kegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Rekap absensi per kegiatan — landscape, urut alfabetik nama.
 * Semua sel ditulis sebagai teks (StringValueBinder) agar NIM tidak berubah format.
 */
class AbsensiRekapExport extends StringValueBinder implements FromArray, WithEvents, WithColumnWidths, WithDrawings, WithCustomValueBinder
{
    private const HEADER_ROW = 7;

    private const LABEL = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];

    private bool $multi;
    private int $jumlahKolom;

    public function __construct(private Kegiatan $kegiatan, private Collection $sesi, private Collection $baris)
    {
        $this->multi       = $sesi->count() > 1;
        $this->jumlahKolom = 4 + ($this->multi ? $sesi->count() + 1 : 1);
    }

    public function array(): array
    {
        $mulai   = $this->kegiatan->tanggal_mulai->locale('id');
        $selesai = $this->kegiatan->tanggal_selesai->locale('id');
        $tanggal = $mulai->translatedFormat('d F Y') . ($mulai->isSameDay($selesai) ? '' : ' – ' . $selesai->translatedFormat('d F Y'));
        $lokasi  = $this->kegiatan->lokasi ? ' · ' . $this->kegiatan->lokasi : '';

        $data = [
            [mb_strtoupper(config('hima.nama'))],
            ['REKAP ABSENSI KEGIATAN'],
            [$this->kegiatan->nama],
            [$tanggal . $lokasi],
            ['Dicetak: ' . now()->locale('id')->translatedFormat('d F Y, H:i') . ' WIB'],
            [''],
            $this->header(),
        ];

        foreach ($this->baris as $i => $b) {
            $data[] = $this->barisData($i + 1, $b);
        }

        return $data;
    }

    private function header(): array
    {
        $kolom = ['No', 'NIM', 'Nama', 'Peserta'];

        if ($this->multi) {
            foreach ($this->sesi as $s) {
                $kolom[] = $s->nama_sesi . "\n" . $s->waktu_mulai->format('d/m H:i');
            }
            $kolom[] = 'Kehadiran';
        } else {
            $kolom[] = 'Status';
        }

        return $kolom;
    }

    private function barisData(int $no, array $b): array
    {
        $baris = [(string) $no, $b['nim'], $b['nama'], $b['tipe'] === 'anggota' ? 'Anggota' : 'Tamu'];

        if ($this->multi) {
            foreach ($this->sesi as $s) {
                $baris[] = self::LABEL[$b['status'][$s->id] ?? ''] ?? '-';
            }
            $baris[] = $b['hadir_semua']
                ? 'Hadir'
                : 'Tidak hadir (' . $b['jumlah_hadir'] . '/' . $this->sesi->count() . ')';
        } else {
            $baris[] = self::LABEL[$b['status'][$this->sesi->first()->id] ?? ''] ?? '-';
        }

        return $baris;
    }

    public function columnWidths(): array
    {
        $lebar = ['A' => 6, 'B' => 14, 'C' => 34, 'D' => 12];

        for ($i = 5; $i <= $this->jumlahKolom; $i++) {
            $lebar[Coordinate::stringFromColumnIndex($i)] = 17;
        }

        return $lebar;
    }

    public function drawings()
    {
        $path = public_path(config('hima.logo'));
        if (!is_file($path)) {
            return [];
        }

        $logo = new Drawing();
        $logo->setName('Logo HIMA');
        $logo->setPath($path);
        $logo->setHeight(70);
        $logo->setCoordinates('A1');
        $logo->setOffsetX(6);
        $logo->setOffsetY(4);

        return $logo;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $akhir = Coordinate::stringFromColumnIndex($this->jumlahKolom);
                $h     = self::HEADER_ROW;
                $last  = $h + $this->baris->count();

                // Judul (baris 1–5)
                for ($r = 1; $r <= 5; $r++) {
                    $sheet->mergeCells("A{$r}:{$akhir}{$r}");
                    $sheet->getRowDimension($r)->setRowHeight(18);
                }
                $sheet->getStyle("A1:{$akhir}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A5')->getFont()->setItalic(true)->setSize(9);

                // Header tabel
                $sheet->getRowDimension($h)->setRowHeight(32);
                $header = $sheet->getStyle("A{$h}:{$akhir}{$h}");
                $header->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                $header->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6B1124');
                $header->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // Isi tabel
                $sheet->getStyle("A{$h}:{$akhir}{$last}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                if ($last > $h) {
                    $sheet->getStyle('A' . ($h + 1) . ":B{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('D' . ($h + 1) . ":{$akhir}{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Cetak: A4 landscape, muat satu halaman lebar
                $setup = $sheet->getPageSetup();
                $setup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $setup->setPaperSize(PageSetup::PAPERSIZE_A4);
                $setup->setFitToPage(true);
                $setup->setFitToWidth(1);
                $setup->setFitToHeight(0);
            },
        ];
    }
}
