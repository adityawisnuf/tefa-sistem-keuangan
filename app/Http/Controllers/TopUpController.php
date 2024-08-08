<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Services\DuitkuService;
use App\Models\PembayaranDuitku;
use App\Models\Siswa;
use App\Models\SiswaWalletRiwayat;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TopUpController extends Controller
{
    protected $duitkuService;

    public function __construct()
    {
        $this->duitkuService = new DuitkuService();
    }

    public function getPaymentMethod()
    {
        $result = $this->duitkuService->getPaymentMethod();
        return response()->json($result['data'], $result['statusCode']);
    }

    public function requestTransaction(TransactionRequest $request)
    {
        $result = $this->duitkuService->requestTransaction($request->validated());
        return response()->json($result['data'], $result['statusCode']);
    }

    public function callback()
    {
        $callbackData = request()->all();
        if (!$this->duitkuService->verifySignature($callbackData))
            return;

        $pembayaranDuitku = PembayaranDuitku::where('merchant_order_id', $callbackData['merchantOrderId'])->first();
        $resultCode = $callbackData['resultCode'] ?? null;

        $pembayaranDuitku->update([
            'callback_response' => json_encode($callbackData),
            'status' => $resultCode
        ]);

        if ($resultCode === '00') {
            $siswaWallet = User::where('email', $callbackData['merchantUserId'])->siswa->siswa_wallet;
            SiswaWalletRiwayat::create([
                'siswa_wallet_id' => $siswaWallet->id,
                'merchant_order_id' => $callbackData['merchantOrderId'],
                'tipe_transaksi' => 'pemasukan',
                'nominal' => $callbackData['amount'],
            ]);
        }
    }

}
