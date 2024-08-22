<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrangTuaRiwayatController extends Controller
{
    public function getRiwayatKantinSiswa($id)
    {
        $orangTua = Auth::user()->orangtua()->with('siswa')->firstOrFail();
        $perPage = request('per_page', 10);

        $siswa = $orangTua->siswa()->findOrFail($id);

        $riwayat = $siswa->kantin_transaksi()
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->with('kantin_transaksi_detail.kantin_produk')
            ->paginate($perPage);

        $formattedData = $riwayat->map(function ($transaksi) {
            return [
                'item_detail' => $transaksi->kantin_transaksi_detail->map(function ($detail) {
                    return [
                        'nama_produk' => $detail->kantin_produk->nama_produk,
                        'harga_jual' => $detail->kantin_produk->harga_jual,
                        'jumlah' => $detail->jumlah,
                        'harga_total' => $detail->harga_jual * $detail->jumlah,
                    ];
                }),
                'status' => $transaksi->status,
            ];
        });

        $paginatedData = new LengthAwarePaginator(
            $formattedData,
            $riwayat->total(),
            $riwayat->perPage(),
            $riwayat->currentPage(),
            [
                'path' => \Request::url(),
                'query' => \Request::query(),
            ]
        );

        return response()->json($paginatedData, Response::HTTP_OK);

    }

    public function getRiwayatLaundrySiswa($id)
    {
        $orangTua = Auth::user()->orangtua()->with('siswa')->firstOrFail();
        $perPage = request('per_page', 10);

        $siswa = $orangTua->siswa()->findOrFail($id);


        $riwayat = $siswa->laundry_transaksi()
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->with('laundry_transaksi_detail.laundry_layanan')
            ->paginate($perPage);

        $formattedData = $riwayat->map(function ($transaksi) {
            return [
                'detail_pesanan' => $transaksi->laundry_transaksi_detail->map(function ($detail) {
                    return [
                        'nama_layanan' => $detail->laundry_layanan->nama_layanan,
                        'harga_jual' => $detail->laundry_layanan->harga,
                        'jumlah' => $detail->jumlah,
                        'harga_total' => $detail->harga_jual * $detail->jumlah,
                    ];
                }),
                'status' => $transaksi->status,
            ];
        });


        $paginatedData = new LengthAwarePaginator(
            $formattedData,
            $riwayat->total(),
            $riwayat->perPage(),
            $riwayat->currentPage(),
            [
                'path' => \Request::url(),
                'query' => \Request::query(),
            ]
        );

        return response()->json($paginatedData, Response::HTTP_OK);

    }
}
