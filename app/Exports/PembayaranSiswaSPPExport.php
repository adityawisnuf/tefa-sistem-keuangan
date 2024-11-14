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

class PembayaranSiswaSPPExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        return view('print.PrintExcelSPP', ['data' => $this->data]);
    }

    private function getNamaBulan($bulan)
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei',
            6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $namaBulan[$bulan] ?? 'Tidak Valid';
    }

    public function styles(Worksheet $worksheet)
    {
        $row = 1;

        foreach ($this->data as $index => $siswa) {
            // Header for student section
            $worksheet->mergeCells("A{$row}:D{$row}");
            $worksheet->setCellValue("A{$row}", "DATA PEMBAYARAN SPP SISWA");
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

            $row += 7;

            // Payment table header
            $worksheet->mergeCells("A{$row}:D{$row}");
            $worksheet->setCellValue("A{$row}", "Pembayaran SPP");
            $worksheet->getStyle("A{$row}:D{$row}")->applyFromArray([
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
            $worksheet->setCellValue("B{$row}", "Bulan");
            $worksheet->setCellValue("C{$row}", "Nominal");
            $worksheet->setCellValue("D{$row}", "Status");

            $worksheet->getStyle("A{$row}:D{$row}")->applyFromArray([
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
                $worksheet->setCellValue("B{$row}", $this->getNamaBulan($payment['bulan'])); // Menggunakan nama bulan
                $worksheet->setCellValue("C{$row}", "Rp. " . number_format($payment['nominal'], 0, ',', '.'));
                $worksheet->setCellValue("D{$row}", $payment['status']);

                $worksheet->getStyle("A{$row}:D{$row}")->applyFromArray([
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

            $row += 2;
        }

        $worksheet->getColumnDimension('A')->setWidth(25);
        $worksheet->getColumnDimension('B')->setWidth(15);
        $worksheet->getColumnDimension('C')->setWidth(20);
        $worksheet->getColumnDimension('D')->setWidth(15);

        $worksheet->getRowDimension(1)->setRowHeight(30);
    }

    public function title(): string
    {
        return 'DATA PEMBAYARAN SPP';
    }
}
