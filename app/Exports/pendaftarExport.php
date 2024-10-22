<?php

namespace App\Exports;

use App\Models\Pendaftar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class pendaftarExport implements FromCollection, WithHeadings, WithStyles
{
    protected $totalNominal;
    protected $selectedYear;

    public function __construct($selectedYear = null)
    {
        $this->selectedYear = $selectedYear;
    }

    public function collection()
    {
        $query = Pendaftar::select(
                'pendaftar.nama_depan',
                'pendaftar.nama_belakang',
                DB::raw("CASE WHEN pendaftar.jenis_kelamin = 1 THEN 'Laki-laki' WHEN pendaftar.jenis_kelamin = 2 THEN 'Perempuan' END as jenis_kelamin"),
                DB::raw("CONCAT('\'', pendaftar.nik) as nik"),
                'pendaftar.alamat',
                DB::raw('IFNULL(SUM(pembayaran.nominal), 0) as nominal')
            )
            ->leftJoin('pembayaran_ppdb', 'pendaftar.ppdb_id', '=', 'pembayaran_ppdb.ppdb_id')
            ->leftJoin('pembayaran', 'pembayaran_ppdb.pembayaran_id', '=', 'pembayaran.id')
            ->groupBy(
                'pendaftar.nama_depan',
                'pendaftar.nama_belakang',
                'pendaftar.jenis_kelamin',
                'pendaftar.nik',
                'pendaftar.alamat'
            );

        // Apply year filter if selected year is provided
        if ($this->selectedYear) {
            $query->whereYear('pendaftar.created_at', $this->selectedYear);
        }

        $pendaftarData = $query->get();

        // Calculate the total nominal
        $this->totalNominal = $pendaftarData->sum('nominal');
        Log::info($pendaftarData);
        return $pendaftarData;
    }

    public function headings(): array
    {
        return [
            'Nama Depan',
            'Nama Belakang',
            'Jenis Kelamin',
            'NIK',
            'Alamat',
            'Nominal',
            'Total Transaksi'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Warna dan gaya untuk header
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['argb' => 'FFFFFF'], // Warna teks putih
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => '3C50E0'], // Warna header biru
            ],
        ]);

        // Warna dan gaya untuk konten
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A2:G$highestRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);

        // Set the format for nominal and total transaction columns as currency
        $sheet->getStyle("F2:F$highestRow")->getNumberFormat()->setFormatCode('Rp #,##0');
        $sheet->getStyle("G2:G$highestRow")->getNumberFormat()->setFormatCode('Rp #,##0');

        // Menempatkan total transaksi di sel G2, tepat di bawah header "Total Transaksi"
        $sheet->setCellValue("G" . ($highestRow + 1), $this->totalNominal);
        $sheet->getStyle("G" . ($highestRow + 1))->getNumberFormat()->setFormatCode('Rp #,##0');

        // Apply border to the Total Transaksi row
        $sheet->getStyle("G" . ($highestRow + 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);

        // Auto size columns to fit the content
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
