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

class OrangTuaWalletController extends Controller
{
    protected $duitkuService;

    public function __construct()
    {
        $this->duitkuService = new DuitkuService();
    }

    public function getDetailWalletSiswa($id)
    {
        $user = Auth::user();
        $siswa = $user->orangtua->first()->siswa->find($id);
        $siswaWallet = $siswa->siswa_wallet->nominal;

        return response()->json(['wallet' => $siswaWallet], 200);
    }

    public function requestTransaction(TopUpOrangTuaRequest $request)
    {
        $orangtua = Auth::user()->orangtua->firstOrFail();

        $fields = $request->validated();
        $siswa = $orangtua->siswa()->findOrFail($fields['siswa_id']);

        $fields['email'] = $siswa->user->email;

        $result = $this->duitkuService->requestTransaction($fields);

        return response()->json($result['data'], $result['statusCode']);
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
