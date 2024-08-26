<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopUpOrangTuaRequest;
use App\Http\Services\DuitkuService;
use App\Models\PembayaranDuitku;
use App\Models\Siswa;
use App\Models\SiswaWalletRiwayat;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OrangTuaWalletController extends Controller
{
    protected $duitkuService;

    public function __construct()
    {
        $this->duitkuService = new DuitkuService();
    }

    public function getWalletSiswa()
    {

        $orangTua = Auth::user()->orangtua->firstOrFail();
        $siswaId = request('siswa_id', null);
        $perPage = request('per_page', 10);

        $siswa = $orangTua->siswa->find($siswaId) ?? $orangTua->siswa->first();
        $riwayat = $siswa->siswa_wallet()->with('siswa_wallet_riwayat')->paginate($perPage);

        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
}
