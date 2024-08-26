<?php

namespace App\Http\Controllers;

use App\Models\KantinPengajuan;
use App\Models\KantinTransaksi;
use App\Models\LaundryPengajuan;
use App\Models\LaundryTransaksi;
use App\Models\LaundryTransaksiKiloan;
use App\Models\LaundryTransaksiSatuan;
use App\Models\UsahaPengajuan;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KepsekController extends Controller
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
        $perPage = request('perpage', 10);
        $role = request('role', 'Kantin');

        $model = $role == 'Kantin' ? new KantinTransaksi : new LaundryTransaksi;

        $transaksi = $model->with([$role == 'Kantin' ? 'kantin_transaksi_detail' : 'laundry_transaksi_detail', 'usaha:id,nama_usaha'])
        ->whereIn('status', ['dibatalkan', 'selesai'])
        ->whereBetween('tanggal_selesai', [$this->startOfMonth, $this->endOfMonth])
        ->paginate($perPage);

        return response()->json($transaksi, Response::HTTP_OK);
    }


    public function getUsahaPengajuan()
    {
        $perPage = request()->input('per_page', 10);

        $pengajuan = UsahaPengajuan::with('usaha:id,nama_usaha')
        ->whereIn('status', ['disetujui', 'ditolak'])
        ->paginate($perPage);

        return response()->json(['data'=>  $pengajuan], Response::HTTP_OK);
    }


}