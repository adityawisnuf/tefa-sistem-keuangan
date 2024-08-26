<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BendaharaLaporanController extends Controller
{
    private $startOfMonth;
    private $endOfMonth;

    public function __construct()
    {
        $this->startOfMonth = now()->startOfMonth();
        $this->endOfMonth = now()->endOfMonth();
    }

    public function getUsahaTransaksi()
    {
        $perPage = request('per_page', 10);
        $role = request('role', 'Kantin');

        $model = $role == 'Kantin' ? new KantinTransaksi : new LaundryTransaksi;
        $transaksi = $model->with([($role == 'Kantin' ? 'kantin_transaksi_detail.kantin_produk' : 'laundry_transaksi_detail.laundry_layanan'), 'usaha:id,nama_usaha'])
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->whereBetween('tanggal_pemesanan', [$this->startOfMonth, $this->endOfMonth])
            ->paginate($perPage);
    
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
}
