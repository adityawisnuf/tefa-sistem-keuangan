<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrangTuaWalletController extends Controller
{
    public function getDetailWalletSiswa($id)
    {
        $user = Auth::user();

        $siswa = $user->orangtua->first()->siswa->find($id);
        $siswaWallet = $siswa->siswa_wallet->nominal;

        return $siswaWallet;
    }

    public function getTransaksiSiswa()
    {
        $user = Auth::user();


    }
}
