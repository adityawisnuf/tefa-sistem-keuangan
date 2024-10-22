<?php

namespace App\Http\Controllers;

use App\Http\Services\DuitkuService;
use App\Models\PembayaranDuitku;
use App\Models\SiswaWalletRiwayat;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TopUpController extends Controller
{
    protected $duitkuService;

    public function __construct()
    {
        $this->duitkuService = new DuitkuService();
    }

    public function getPaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'paymentAmont' => ['integer', 'min:1']
        ]);

        $paymentAmount = $validated['paymentAmount'] ?? 0;
        $result = $this->duitkuService->getPaymentMethod($paymentAmount);
        return response()->json($result['data'], $result['statusCode']);
    }

    public function requestTransaction(Request $request)
    {
        $rules = [
            'siswa_id' => ['exists:siswa,id', Rule::requiredIf(Auth::user()->role == 'OrangTua')],
            'paymentAmount' => ['required', 'numeric', 'min:1'],
            'paymentMethod' => ['required'],
        ];

        $validated = $request->validate($rules);

        $user = isset($validated['siswa_id'])
            ? Auth::user()->orangtua->siswa()->findOrFail($validated['siswa_id'])->user
            : Auth::user();

        $validated['email'] = $user->email;
        $validated['additionalParam'] = json_encode([
            'type' => 'topup',
            'data' => $user->email
        ]);
        $result = $this->duitkuService->requestTransaction($validated);
        return response()->json($result['data'], $result['statusCode']);
    }

    public function callback(Request $request)
    {
        $callbackData = $request->all();

        if (!$this->duitkuService->verifySignature($callbackData))
            return;

        $resultCode = $callbackData['resultCode'] ?? null;

        $email = json_decode($callbackData['additionalParam'], true)['data'];

        DB::beginTransaction();
        try {
            $pembayaranDuitku = PembayaranDuitku::findOrFail($callbackData['merchantOrderId'])->first();
            $pembayaranDuitku->update([
                'callback_response' => json_encode($callbackData),
                'status' => $resultCode
            ]);

            if ($resultCode === '00') {
                $siswaWallet = User::where('email', $email)->first()->siswa->siswa_wallet;
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
