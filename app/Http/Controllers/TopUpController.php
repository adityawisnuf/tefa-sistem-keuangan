<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopUpRequest;
use App\Http\Services\DuitkuService;
use App\Models\PembayaranDuitku;
use App\Models\Siswa;
use App\Models\SiswaWalletRiwayat;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TopUpController extends Controller
{
    protected $duitkuService;

    public function __construct()
    {
        $this->duitkuService = new DuitkuService();
    }

    public function getPaymentMethod()
    {
        $paymentAmount = request('paymentAmount', 0);
        $result = $this->duitkuService->getPaymentMethod($paymentAmount);
        return response()->json($result['data'], $result['statusCode']);
    }

    public function requestTransaction(TopUpRequest $request)
    {
        $fields = $request->validated();

        $user = isset($fields['siswa_id'])
            ? Auth::user()->orangtua->firstOrFail()->siswa->findOrFail($fields['siswa_id'])->user
            : Auth::user();
        
        $fields['email'] = $user->email;
        $result = $this->duitkuService->requestTransaction($fields);
        return response()->json($result['data'], $result['statusCode']);
    }

    public function callback()
    {
        $callbackData = request()->all();

        if (!$this->duitkuService->verifySignature($callbackData)) return;

        $resultCode = $callbackData['resultCode'] ?? null;
        Log::info(json_encode($callbackData));

        DB::beginTransaction();
        try {
            $pembayaranDuitku = PembayaranDuitku::findOrFail($callbackData['merchantOrderId'])->first();
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
