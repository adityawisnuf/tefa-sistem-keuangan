<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use App\Models\UsahaPengajuan;
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
        $namaUsaha = request('nama_usaha');

        $model = $role == 'Kantin' ? new KantinTransaksi : new LaundryTransaksi;

        $transaksi = $model->with([$role == 'Kantin' ? 'kantin_transaksi_detail' : 'laundry_transaksi_detail', 'usaha:id,nama_usaha'])
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->whereBetween('tanggal_selesai', [$this->startOfMonth, $this->endOfMonth])
            ->when($namaUsaha, function ($query) use ($namaUsaha) {
                $query->whereRelation("usaha", 'nama_usaha', 'like', '%' . $namaUsaha . '%');
            })
            ->paginate($perPage);

        return response()->json(["data" => $transaksi], Response::HTTP_OK);
    }


    public function getUsahaPengajuan()
    {
        $perPage = request()->input('per_page', 10);
        $namaUsaha = request('nama_usaha');

        $pengajuan = UsahaPengajuan::with('usaha:id,nama_usaha')
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->when($namaUsaha, function ($query) use ($namaUsaha) {
                $query->whereRelation("usaha", 'nama_usaha', 'like', '%' . $namaUsaha . '%');
            })
            ->paginate($perPage);

        return response()->json(['data' => $pengajuan], Response::HTTP_OK);
    }


}