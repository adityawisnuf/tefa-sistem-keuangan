<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembayaranExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $totalTransaksi;
    protected $selectedYear;

    public function __construct($selectedYear = null)
    {
        $this->selectedYear = $selectedYear;
    }

    public function collection()
    {
        // Initialize query to fetch pembayaran data
        $query = Pembayaran::join('pembayaran_ppdb', 'pembayaran.id', '=', 'pembayaran_ppdb.pembayaran_id')
            ->join('pendaftar', 'pembayaran_ppdb.ppdb_id', '=', 'pendaftar.ppdb_id')
            ->select(
                'pendaftar.nama_depan',
                'pendaftar.nama_belakang',
                'pembayaran_ppdb.status',
                'pembayaran.nominal'
            );

        // Apply year filter if selectedYear is provided and not empty
        if (!empty($this->selectedYear)) {
            $query->whereYear('pembayaran.created_at', $this->selectedYear);
        }

        $data = $query->get();

        // Calculate total transactions
        $this->totalTransaksi = $data->sum('nominal');

        return $data;
    }

    public function map($row): array
    {
        static $firstRow = true;
        $statusText = $this->getStatusText($row->status);
        $totalTransaksi = $firstRow ? $this->totalTransaksi : ''; // Display total only in the first row
        $firstRow = false;

        return [
            $row->nama_depan,
            $row->nama_belakang,
            $statusText,
            $row->nominal,
            $totalTransaksi,
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Depan',
            'Nama Belakang',
            'Status',
            'Nominal',
            'Total Transaksi',
        ];
    }

    private function getStatusText($status)
    {
        $statusTexts = [
            1 => 'Mendaftar',
            2 => 'Telah Membayar',
            3 => 'Telah Terdaftar',
            4 => 'Ditolak',
        ];

        return $statusTexts[$status] ?? 'Unknown';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['argb' => 'FFFFFF'], // White text color
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => '3C50E0'], // Blue header color
            ],
        ]);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A2:E$highestRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);

        // Set the format for nominal and total transaction columns as currency
        $sheet->getStyle("D2:D$highestRow")->getNumberFormat()->setFormatCode('Rp #,##0');
        $sheet->getStyle("E2:E$highestRow")->getNumberFormat()->setFormatCode('Rp #,##0');

        // Auto size columns to fit the content
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}

