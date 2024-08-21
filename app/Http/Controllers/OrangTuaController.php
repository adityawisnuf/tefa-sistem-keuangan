<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrangTuaController extends Controller
{
    public function getWalletSiswa(Request $request)
    {

        $orangTua = Auth::user()->orangtua()->with('siswa')->firstOrFail();

        $siswaId = $request->input('siswa_id');
        $siswa = $orangTua->siswa()->findOrFail($siswaId);

        $perPage = $request->input('per_page', 10);

        $riwayat = $siswa->siswa_wallet()
            ->with('siswa_wallet_riwayat')
            ->paginate($perPage);

        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }

    public function getRiwayatKantin(Request $request){
        $orangTua = Auth::user()->orangtua()->with('siswa')->firstOrFail();

        $siswaId = $request->input('siswa_id');
        $siswa = $orangTua->siswa()->findOrFail($siswaId);

        $perPage = $request->input('per_page', 10);

        $riwayat = $siswa->kantin_transaksi()
            ->with('kantin_transaksi_detail.kantin_produk')
            ->paginate($perPage);

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
    
    public function getRiwayatLaundry(Request $request){
        $orangTua = Auth::user()->orangtua()->with('siswa')->firstOrFail();

        $siswaId = $request->input('siswa_id');
        $siswa = $orangTua->siswa()->findOrFail($siswaId);

        $perPage = $request->input('per_page', 10);

        $riwayat = $siswa->laundry_transaksi()
            ->with('laundry_transaksi_detail.laundry_layanan')
            ->paginate($perPage);

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
}