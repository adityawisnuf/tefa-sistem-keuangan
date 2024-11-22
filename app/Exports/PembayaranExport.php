<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PembayaranExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected $pembayarans;

    public function __construct($pembayarans)
    {
        $this->pembayarans = $pembayarans;
    }

    /**
     * Membuat tampilan untuk ekspor.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function view(): \Illuminate\Contracts\View\View
    {
        return view('print.printExcelBukuKas', ['pembayarans' => $this->pembayarans]);
    }

    /**
     * Menambahkan gaya ke worksheet.
     *
     * @param Worksheet $worksheet
     */
    public function styles(Worksheet $worksheet)
    {
        // Menambahkan judul di bagian atas
        $worksheet->mergeCells('A1:G1');
        $worksheet->setCellValue('A1', 'Laporan Buku Kas');
        $worksheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
        ]);

        // Menambahkan baris kosong untuk jarak
        $worksheet->getRowDimension(2)->setRowHeight(20);

        // Styling header tabel (baris ke-2)
        $worksheet->getStyle('A2:G2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
        ]);

        // Styling semua sel data tanpa memodifikasi styling header
        // Ini memastikan gaya header tabel tetap dan data tabel menggunakan gaya default
        $worksheet->getStyle('A2:G' . $worksheet->getHighestRow())->applyFromArray([
            'font' => [
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            
        ]);
        // $worksheet->getStyle('A:G' . $worksheet->getHighestRow())->applyFromArray([
        //     'borders' => [
        //         'allBorders' => [
        //             'borderStyle' => Border::BORDER_THIN,
        //         ],
        //     ],
        //     'alignment' => [
        //         'horizontal' => Alignment::HORIZONTAL_LEFT,
        //         'vertical' => Alignment::VERTICAL_CENTER,
        //         'wrapText' => true, // Membungkus teks di dalam sel
        //     ],
        // ]);
    }

    /**
     * Judul worksheet
     *
     * @return string
     */
    public function title(): string
    {
        return 'Laporan Buku Kas';
    }
}
