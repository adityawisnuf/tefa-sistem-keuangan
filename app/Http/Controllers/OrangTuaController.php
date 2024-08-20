<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class OrangTuaController extends Controller
{
    public function getKantinRiwayat(Request $request)
    {
        $orangTua = Auth::user()->orang_tua()->firstOrFail();
        $siswa = $orangTua->siswa()->firstOrFail();  
        $perPage = $request->input('per_page', 10);

        $riwayat = $siswa->kantin_transaksi()->with('kantin_transaksi_detail.kantin_produk')->latest()->paginate($perPage);

        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
}