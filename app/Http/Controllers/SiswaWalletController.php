<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SiswaWalletController extends Controller
{
    public function getSaldo()
    {
        $siswaWallet = Auth::user()->siswa->first()->siswa_wallet;
        return response()->json(['data' => $siswaWallet->nominal], Response::HTTP_OK);
    }

    public function getRiwayat()
    {
        $siswaWallet = Auth::user()->siswa->first()->siswa_wallet;
        $perPage = request()->input('per_page', 10);
        $siswaWalletRiwayat = $siswaWallet->siswa_wallet_riwayat()->latest()->paginate($perPage);
        return response()->json(['data' => $siswaWalletRiwayat], Response::HTTP_OK);
    }
}
