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
use Illuminate\Support\Facades\Validator;
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

        $validator = Validator::make(request()->all(), [
            'siswa_id' => ['nullabe', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $orangTua = Auth::user()->orangtua->firstOrFail();
        $siswaId = request('siswa_id', null);
        $perPage = request('per_page', 10);

        try{
            $siswa = $orangTua->siswa->find($siswaId) ?? $orangTua->siswa->first();
            $riwayat = $siswa->siswa_wallet()->with('siswa_wallet_riwayat')->paginate($perPage);

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
        } catch(Exception $e){
            Log::error('getWalletSiswa: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data wallet siswa: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}