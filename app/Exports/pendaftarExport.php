<?php

namespace App\Exports;

use App\Models\Pendaftar;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class pendaftarExport implements FromCollection, WithHeadings, WithStyles
{
    protected $totalNominal;

    public function collection()
    {
        $pendaftarData = Pendaftar::select(
                'pendaftar.nama_depan',
                'pendaftar.nama_belakang',
                DB::raw("CASE WHEN pendaftar.jenis_kelamin = 1 THEN 'Laki-laki' WHEN pendaftar.jenis_kelamin = 2 THEN 'Perempuan' END as jenis_kelamin"),
                DB::raw("CONCAT('\'', pendaftar.nik) as nik"), // Menambahkan tanda kutip di depan NIK
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
            )
            ->get();

        // Calculate the total nominal
        $this->totalNominal = $pendaftarData->sum('nominal');

        return $pendaftarData;
    }
{
    return Pendaftar::select(
            'pendaftar.nama_depan',
            'pendaftar.nama_belakang',
            'pendaftar.jenis_kelamin',
            DB::raw("CONCAT('\'', pendaftar.nik) as nik"), // Menambahkan tanda kutip di depan NIK
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
        )
        ->get();
}

public function map($row): array
{
    $jenisKelamin = $this->getStatusText($row->jenis_kelamin);

    return [
        $row->nama_depan,
        $row->nama_belakang,
        $jenisKelamin,
        $row->nik,
        $row->alamat,
        $row->nominal,
    ];
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
            'Total Transaksi', // New column
        ];
    }

    private function getStatusText($jenis_kelamin)
    {
        $jenisKelamin = [
            1 => 'laki-laki',
            2 => 'perempuan',

        ];

        return $jenisKelamin[$jenis_kelamin] ?? 'Unknown';
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

        // Set the Total Transaksi in the last row
        $sheet->setCellValue("G" . ($highestRow + 1), $this->totalNominal);
    }
}
