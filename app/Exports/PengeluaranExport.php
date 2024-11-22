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

class PengeluaranExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected $pengeluaran;

    public function __construct($pengeluaran)
    {
        $this->pengeluaran = $pengeluaran;
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        return view('print.printExcelPengeluaran', ['pengeluaran' => $this->pengeluaran]);
    }


    /**
     * Menambahkan gaya ke worksheet.
     *
     * @param Worksheet $worksheet
     */
    public function styles(Worksheet $worksheet)
    {
        // Menambahkan judul di bagian atas
        $worksheet->mergeCells('A1:F1');
        $worksheet->setCellValue('A1', 'Laporan Pengeluaran');
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

        // Styling header tabel
        $worksheet->getStyle('A2:F2')->applyFromArray([
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

        // Styling untuk sel data
        $worksheet->getStyle('A2:F' . $worksheet->getHighestRow())->applyFromArray([
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
    }

    /**
     * Judul worksheet
     *
     * @return string
     */
    public function title(): string
    {
        return 'Laporan Pengeluaran';
    }
}
