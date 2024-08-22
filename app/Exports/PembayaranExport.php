<?php

namespace App\Exports;

use App\Models\PembayaranPpdb;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

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
}
