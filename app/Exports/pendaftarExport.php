<?php

namespace App\Exports;

use App\Models\Pendaftar;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class pendaftarExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $data = Pendaftar::select(
                'pendaftar.nama_depan',
                'pendaftar.nama_belakang',
                'pendaftar.jenis_kelamin',
                'pendaftar.nik',
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

        return $data;
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
        ];
    }
}
