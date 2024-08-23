<?php

namespace App\Exports;

use App\Models\PembayaranPpdb;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembayaranExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return PembayaranPpdb::join('pendaftar', 'pembayaran_ppdb.ppdb_id', '=', 'pendaftar.ppdb_id')
            ->select(
                'pendaftar.nama_depan',
                'pendaftar.nama_belakang',
                'pembayaran_ppdb.status',
                DB::raw('SUM(pembayaran_ppdb.nominal) as total_transaksi')
                )
                ->groupBy(
                    'pendaftar.nama_depan',
                    'pendaftar.nama_belakang',
                    'pembayaran_ppdb.status'
                    )
                    ->get();
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


    public function styles(Worksheet $sheet)
    {
        return [
            // Gaya untuk header (baris 1)
            1 => ['font' => ['bold' => true, 'size' => 12]],

            // Menambahkan border untuk seluruh kolom
            'A1:E1' => [
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ],

            // Menambahkan background color untuk header
            'A1:E1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFFF00'],
                ],
            ],
        ];
    }
}
