<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TopUpRequest;
use App\Http\Services\DuitkuService;
use App\Models\PembayaranDuitku;
use App\Models\Siswa;
use App\Models\SiswaWalletRiwayat;
use App\Models\User;
use Exception;
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

        return $siswaWallet;
    }

    public function getPaymentMethod(TopUpRequest $request)
    {
        $fields = $request->validated();
        $result = $this->duitkuService->getPaymentMethod($fields['paymentAmount']);
        return response()->json($result['data'], $result['statusCode']);
    }

    public function requestTransaction(TopUpRequest $request)
    {
        $user = Auth::user();

        $fields = $request->validated();

        $siswa = Siswa::where('orangtua_id', $user->id)
            ->where('id', $fields['siswa_id'])  
            ->first();

        if (!$siswa) {
            return response()->json(['error' => 'Siswa tidak ditemukan atau tidak terkait dengan orang tua ini'], 404);
        }

        $fields['email'] = $siswa->user->email; 
        $fields['additionalParam'] = $siswa->user->email; 
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
                'status' => $resultCode
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
    