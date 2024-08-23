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

    public function callback()
    {
        $callbackData = request()->all();

        if (!$this->duitkuService->verifySignature($callbackData)) {
            return;
        }

        $resultCode = $callbackData['resultCode'] ?? null;

        try {
            $pembayaranDuitku = PembayaranDuitku::where('merchant_order_id', $callbackData['merchantOrderId'])->first();

            DB::beginTransaction();
            $pembayaranDuitku->update([
                'callback_response' => json_encode($callbackData),
                'status' => $resultCode,
            ]);

            if ($resultCode === '00') {
                $siswaWallet = User::where('email', $callbackData['additionalParam'])->first()->siswa->first()->siswa_wallet;

                SiswaWalletRiwayat::create([
                    'siswa_wallet_id' => $siswaWallet->id,
                    'merchant_order_id' => $callbackData['merchantOrderId'],
                    'tipe_transaksi' => 'pemasukan',
                    'nominal' => $callbackData['amount'],
                ]);

                $siswaWallet->update([
                    'nominal' => $siswaWallet->nominal + $callbackData['amount'],
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error memperbarui transaksi: ' . $e);
        }
    }
}
