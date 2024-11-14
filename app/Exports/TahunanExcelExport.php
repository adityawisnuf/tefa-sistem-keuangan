<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TahunanExcelExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        return view('print.printExcelTahunan', ['data' => $this->data]);
    }

    public function styles(Worksheet $worksheet)
    {
        $row = 1;

        foreach ($this->data as $index => $siswa) {
            // Header for student section
            $worksheet->mergeCells("A{$row}:C{$row}");
            $worksheet->setCellValue("A{$row}", "DATA PEMBAYARAN TAHUNAN SISWA");
            $worksheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50'],
                ],
            ]);
            $row++;

            // Student details section
            $worksheet->setCellValue("A{$row}", "Nama Siswa: {$siswa['nama_siswa']}");
            $worksheet->setCellValue("A" . ($row + 1), "Kelas: {$siswa['kelas']}");
            $worksheet->setCellValue("A" . ($row + 2), "Jurusan: {$siswa['jurusan']}");
            $worksheet->setCellValue("A" . ($row + 3), "Telepon: {$siswa['telepon']}");
            $worksheet->setCellValue("A" . ($row + 4), "Orang Tua: {$siswa['orangtua']}");
            $worksheet->setCellValue("A" . ($row + 5), "Sisa Tagihan: Rp. " . number_format($siswa['sisa_tagihan'] ?? 0, 0, ',', '.'));

            $worksheet->getStyle("A{$row}:A" . ($row + 5))->applyFromArray([
                'font' => ['size' => 12],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            $row += 7; // Move down after student details

            // Payment table header
            $worksheet->mergeCells("A{$row}:C{$row}");
            $worksheet->setCellValue("A{$row}", "Pembayaran Tahunan");
            $worksheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF9800'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            $row++;

            // Payment column headers
            $worksheet->setCellValue("A{$row}", "Pembayaran Ke");
            $worksheet->setCellValue("B{$row}", "Nominal");
            $worksheet->setCellValue("C{$row}", "Status");

            $worksheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            $row++;

            // Payment data rows
            foreach ($siswa['payments'] as $payment) {
                $worksheet->setCellValue("A{$row}", $payment['pembayaran_ke']);
                $worksheet->setCellValue("B{$row}", "Rp. " . number_format($payment['nominal'], 0, ',', '.'));
                $worksheet->setCellValue("C{$row}", $payment['status']);

                $worksheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => ['size' => 12],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
                $row++;
            }

            // Add spacing between student records
            $row += 2;
        }

        // Adjust column width for better appearance
        $worksheet->getColumnDimension('A')->setWidth(25);
        $worksheet->getColumnDimension('B')->setWidth(20);
        $worksheet->getColumnDimension('C')->setWidth(15);

        // Set the height of the header row for better visibility
        $worksheet->getRowDimension(1)->setRowHeight(30);
    }

    public function title(): string
    {
        return 'DATA PEMBAYARAN TAHUNAN';
    }
}
