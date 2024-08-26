<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use App\Models\UsahaPengajuan;
use Symfony\Component\HttpFoundation\Response;

class KepsekLaporanController extends Controller
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
        $perPage = request('per_xpage', 10);
        $role = request('role', 'Kantin');
        $nama_usaha = request('nama_usaha', null);

        $model = $role == 'Kantin' ? new KantinTransaksi : new LaundryTransaksi;

        $transaksi = $model->with([$role == 'Kantin' ? 'kantin_transaksi_detail.kantin_produk' : 'laundry_transaksi_detail.laundry_layanan', 'usaha:id,nama_usaha'])
        ->when($nama_usaha, function($query) use ($nama_usaha) {
            $query->whereRelation('usaha', 'nama_usaha', 'like', '%' . $nama_usaha . '%');
        })
        ->whereIn('status', ['dibatalkan', 'selesai'])
        ->whereBetween('tanggal_selesai', [$this->startOfMonth, $this->endOfMonth])
        ->paginate($perPage);

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
}
