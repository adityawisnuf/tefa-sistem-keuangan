<?php

namespace App\Http\Controllers;

use App\Models\PembayaranDuitku;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\TransferException;

class PembayaranController extends Controller
{
    public function getPaymentMethods(Request $request)
    {
        $merchantCode = $request->input('merchantcode');
        $apiKey = $request->input('apikey');
        $datetime = now()->format('Y-m-d H:i:s');
        $paymentAmount = $request->input('amount');
        $signature = hash('sha256', $merchantCode . $paymentAmount . $datetime . $apiKey);

        $params = [
            'merchantcode' => $merchantCode,
            'amount' => $paymentAmount,
            'datetime' => $datetime,
            'signature' => $signature,
        ];

        $client = new Client();
        $response = $client->post('https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $params,
            'verify' => false
        ]);

        if ($response->getStatusCode() == 200) {
            return response()->json(json_decode($response->getBody(), true));
        } else {
            return response()->json(['error' => 'Server Error', 'message' => json_decode($response->getBody())->Message], $response->getStatusCode());
        }
    }

    public function createTransaction(Request $request)
    {
        // Ambil data dari request
        $merchantCode = $request->input('merchantCode');
        $apiKey = $request->input('apiKey');
        $paymentAmount = $request->input('paymentAmount');
        $paymentMethod = $request->input('paymentMethod');
        $merchantOrderId = $request->input('merchantOrderId');
        $productDetails = $request->input('productDetails');
        $email = $request->input('email');
        $phoneNumber = $request->input('phoneNumber');
        $additionalParam = $request->input('additionalParam');
        $merchantUserInfo = $request->input('merchantUserInfo');
        $customerVaName = $request->input('customerVaName');
        $callbackUrl = $request->input('callbackUrl');
        $returnUrl = $request->input('returnUrl');
        $expiryPeriod = $request->input('expiryPeriod');
        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

        Log::info('Signature generated in createTransaction', ['signature' => $signature]);

        // Detail alamat
        $address = [
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'postalCode' => $request->input('postalCode'),
            'phone' => $phoneNumber,
            'countryCode' => $request->input('countryCode')
        ];

        // Detail pelanggan
        $customerDetail = [
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'billingAddress' => $address,
            'shippingAddress' => $address
        ];

        // Detail item
        $itemDetails = $request->input('itemDetails'); // Pastikan ini adalah array

        $params = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => $additionalParam,
            'merchantUserInfo' => $merchantUserInfo,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'itemDetails' => $itemDetails,
            'customerDetail' => $customerDetail,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod
        ];

        $client = new Client();

        try {
            $response = $client->post('https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
                'verify' => false
            ]);

            // Cek jika status code 200
            if ($response->getStatusCode() == 200) {
                $responseBody = json_decode($response->getBody(), true);
                $responseBody['signature'] = $signature; // Tambahkan signature ke response

                // Simpan data transaksi ke dalam database
                PembayaranDuitku::create([
                    'merchant_order_id' => $merchantOrderId,
                    'reference' => $responseBody['reference'],
                    'payment_method' => $paymentMethod,
                    'transaction_response' => json_encode($responseBody),
                    'callback_response' => null,
                    'status' => 'pending',
                ]);

                return response()->json($responseBody);
            } else {
                return response()->json([
                    'error' => 'Server Error',
                    'message' => json_decode($response->getBody())->Message
                ], $response->getStatusCode());
            }
        } catch (RequestException $e) {
            // Tangani exception jika terjadi kesalahan pada request
            Log::error('Request Error in createTransaction', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Request Error',
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function handleCallback(Request $request)
    {
        $apiKey = '8093b2c02b8750e4e73845f307325566'; // API key anda
        $merchantCode = $request->input('merchantCode');
        $amount = $request->input('amount');
        $merchantOrderId = $request->input('merchantOrderId');
        $signature = $request->input('signature');

        Log::info('Data received from Duitku', [
            'merchantCode' => $merchantCode,
            'amount' => $amount,
            'merchantOrderId' => $merchantOrderId,
            'signature' => $signature,
        ]);
        $params = $merchantCode . $merchantOrderId . $amount . $apiKey;
        $calcSignature = md5($params);

        Log::info('Calculated Signature', ['calcSignature' => $calcSignature]);

        if ($signature == $calcSignature) {
            // Callback tervalidasi, proses transaksi sesuai kebutuhan Anda
            Log::info("Callback valid untuk Order ID: $merchantOrderId, Amount: $amount");

            // Update status transaksi di database
            $pembayaran = PembayaranDuitku::where('merchant_order_id', $merchantOrderId)->first();

            if ($pembayaran) {
                try {
                    $pembayaran->update([
                        'callback_response' => json_encode($request->all()),
                        'status' => 'success'
                    ]);
                    Log::info("Update successful for Order ID: $merchantOrderId");
                } catch (\Exception $e) {
                    Log::error("Error updating payment record: " . $e->getMessage());
                    return response()->json(['error' => 'Update Error', 'message' => $e->getMessage()], 500);
                }
            } else {
                Log::error("Payment record not found for Order ID: $merchantOrderId");
                return response()->json(['error' => 'Payment record not found'], 404);
            }

            return response()->json(['message' => 'Callback processed successfully'], 200);
        } else {
            Log::error("Bad signature for Order ID: $merchantOrderId");
            return response()->json(['error' => 'Bad signature'], 400);
        }
    }
}
