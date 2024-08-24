<?php
namespace App\Exports;

use App\Models\PembayaranPpdb;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembayaranExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $totalTransaksi;

    public function collection()
    {
        // Ambil data transaksi
        $data = PembayaranPpdb::join('pendaftar', 'pembayaran_ppdb.ppdb_id', '=', 'pendaftar.ppdb_id')
            ->select(
                'pendaftar.nama_depan',
                'pendaftar.nama_belakang',
                'pembayaran_ppdb.status',
                'pembayaran_ppdb.nominal'
            )
            ->get();

        // Hitung total transaksi
        $this->totalTransaksi = $data->sum('nominal');

        return $data;
    }

    public function map($row): array
    {
        static $firstRow = true;
        $statusText = $this->getStatusText($row->status);
        $totalTransaksi = $firstRow ? $this->totalTransaksi : ''; // Tampilkan total transaksi hanya di baris pertama
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
}
