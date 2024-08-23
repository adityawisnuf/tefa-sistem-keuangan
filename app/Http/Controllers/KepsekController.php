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
    private $startOfWeek;
    private $endOfWeek;

    public function __construct()
    {
        $this->startOfWeek = now()->startOfWeek();
        $this->endOfWeek = now()->endOfWeek();
    }

    // public function getUsahaTransaksi() {
    //     $perPage = request('perpage', 10);
    //     $role = request('role', 'Kantin');


    //     $model = $role == 'Kantin' ? New KantinTransaksi : New LaundryTransaksi;
    //     $transaksi = $model->with($role == 'Kantin' ? 'kantin_transaksi_detail' : 'laundry_transaksi_detail')
    //         ->whereIn('status', ['dibatalkan', 'selesai'])
    //         ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
    //         ->paginate($perPage);    

    //     return response()->json(['data' => $transaksi], Response::HTTP_OK);
    // }

    public function getUsahaTransaksi()
    {
        $perPage = request('perpage', 10);
        $role = request('role', 'Kantin');

        $model = $role == 'Kantin' ? new KantinTransaksi : new LaundryTransaksi;

        $transaksi = $model->with([$role == 'Kantin' ? 'kantin_transaksi_detail' : 'laundry_transaksi_detail', 'usaha'])
        ->whereIn('status', ['dibatalkan', 'selesai'])
        ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
        ->paginate($perPage);

        $transaksi->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'usaha_id' => $item->usaha_id,
                'nama_usaha' => $item->usaha ? $item->usaha->nama_usaha : null,
                'status' => $item->status,
                'tanggal_selesai' => $item->tanggal_selesai,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'detail_transaksi' => $item->kantin_transaksi_detail ?? $item->laundry_transaksi_detail,
            ];
        });

        return response()->json($transaksi, Response::HTTP_OK);
    }

    // public function getUsahaPengajuan()
    // {
    //     $perPage = request()->input('per_page', 10);
    //     return UsahaPengajuan::whereIn('status', ['disetujui', 'ditolak'])->paginate($perPage);
    // }


    public function getUsahaPengajuan()
    {
        $perPage = request()->input('per_page', 10);

        $pengajuan = UsahaPengajuan::whereIn('status', ['disetujui', 'ditolak'])
            ->with('usaha')
            ->paginate($perPage);

        $pengajuan->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'usaha_id' => $item->usaha_id,
                'nama_usaha' => $item->usaha ? $item->usaha->nama_usaha : null,
                'jumlah_pengajuan' => $item->jumlah_pengajuan,
                'status' => $item->status,
                'alasan_penolakan' => $item->alasan_penolakan,
                'tanggal_pengajuan' => $item->tanggal_pengajuan,
                'tanggal_selesai' => $item->tanggal_selesai,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return response()->json($pengajuan, Response::HTTP_OK);
    }




}