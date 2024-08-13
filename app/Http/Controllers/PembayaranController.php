<?php

namespace App\Http\Controllers;

use App\Models\PembayaranDuitku;
use App\Models\PembayaranPpdb;
use App\Models\Pembayaran;
use App\Models\Ppdb;
use App\Models\PembayaranKategori;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{


    public function getPaymentMethod(Request $request)
    {
        // Validate input from request
        $request->validate([
            'merchantCode' => 'string',
            'apiKey' => 'string',
            'paymentAmount' => 'numeric',
            'paymentMethod' => 'nullable|string', // Allow optional paymentMethod param
        ]);
    
        $merchantCode = "DS19869";
        $apiKey ="8093b2c02b8750e4e73845f307325566";
        $paymentAmount = 10000;
        $paymentMethod = $request->get('paymentMethod'); 
        $datetime = now()->format('Y-m-d H:i:s');
        $signature = hash('sha256', $merchantCode . $paymentAmount . $datetime . $apiKey);
    
        // Generate signature
        $signature = hash('sha256', $merchantCode . $paymentAmount . $datetime . $apiKey);
    
        // Prepare request parameters
        $params = [
            'merchantcode' => $merchantCode,
            'amount' => $paymentAmount,
            'datetime' => $datetime,
            'signature' => $signature,
            'paymentMethod' => $paymentMethod
        ];
    
        // Build the API URL with optional payment method parameter
        $url = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';
    
        $client = new Client();
    
        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen(json_encode($params)),
                ],
                'body' => json_encode($params),
                'verify' => false,
            ]);
    
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
    
            if ($statusCode == 200) {
                return response()->json($responseBody, 200);
            } else {
                return response()->json([
                    'error' => 'Server Error',
                    'message' => $responseBody['Message'] ?? 'An error occurred',
                ], $statusCode);
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving payment methods from Duitku', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Request Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    // Method untuk membuat transaksi
    public function createTransaction(Request $request)
    {
        // Ambil data dari request
        $merchantCode = "DS19869";
        $apiKey ="8093b2c02b8750e4e73845f307325566";
        $paymentAmount = 10000;
        $first_name = $request->input('nama_depan');
        $last_name = $request->input('nama_belakang');
        $paymentMethod = $request->input('paymentMethod');
        $merchantOrderId = Str::uuid();
        $callbackUrl = 'https://c127-180-244-134-195.ngrok-free.app/api/payment-callback';
        $returnUrl = 'http://localhost:5173/';
        $expiryPeriod = 60;
        $customerEmail = $request->input('email');
        $customerVaName = $first_name . ' ' . $last_name;
        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);
    
        Log::info('Signature generated in createTransaction', ['signature' => $signature]);
    
        $params = [
            'merchantCode' => $merchantCode,
            'nama_depan' => $first_name,
            'nama_belakang' => $last_name,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod,
            'email' => $customerEmail,
            'customerVaName' => $customerVaName
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
    
            if ($response->getStatusCode() == 200) {
                $responseBody = json_decode($response->getBody(), true);
                $responseBody['signature'] = $signature;
                $responseBody['merchantOrderId'] = $merchantOrderId;
    
                $pembayaranKategori = PembayaranKategori::create([
                    'nama' => $request->input('nama_kategori'),
                    'jenis_pembayaran' => $request->input('jenis_pembayaran'),
                    'tanggal_pembayaran' => now(),
                    'status' => 1
                ]);
    
                $pembayaranDuitku = PembayaranDuitku::create([
                    'merchant_order_id' => $merchantOrderId,
                    'reference' => $responseBody['reference'],
                    'payment_method' => $paymentMethod,
                    'transaction_response' => json_encode($responseBody),
                    'callback_response' => null,
                    'status' => 'pending',
                ]);
    
                $ppdb = Ppdb::create([
                    'status' => 1,
                    'merchant_order_id' => $merchantOrderId,
                ]);
    
                $request->session()->put('ppdb_id', $ppdb->id);
    
                $pembayaran = Pembayaran::create([
                    'siswa_id' => null,
                    'pembayaran_kategori_id' => $pembayaranKategori->id,
                    'nominal' => $paymentAmount,
                    'status' => 0,
                    'kelas_id' => null,
                    'ppdb_id' => $ppdb->id 
                ]);

                PembayaranPpdb::create([
                    'ppdb_id' => $ppdb->id,
                    'pembayaran_id' => $pembayaran->id,
                    'nominal' => $paymentAmount,
                    'merchant_order_id' => $merchantOrderId,
                    'status' => 0,
                ]);
    
                return response()->json($responseBody);
            } else {
                return response()->json([
                    'error' => 'Server Error',
                    'message' => json_decode($response->getBody())->Message
                ], $response->getStatusCode());
            }
        } catch (RequestException $e) {
            Log::error('Request Error in createTransaction', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Request Error',
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
    




    // Method untuk menangani callback
    public function handleCallback(Request $request)
    {
        try {
            $apiKey = '8093b2c02b8750e4e73845f307325566';
            $merchantCode = $request->input('merchantCode');
            $PaymentAmount = $request->input('PaymentAmount');
            $merchantOrderId = $request->input('merchantOrderId');
            $signature = $request->input('signature');

            Log::info('Data received from Duitku', [
                'merchantCode' => $merchantCode,
                'PaymentAmount' => $PaymentAmount,
                'merchantOrderId' => $merchantOrderId,
                'signature' => $signature,
            ]);

            $params = $merchantCode . $PaymentAmount . $merchantOrderId . $apiKey;
            $calcSignature = md5($params);

            Log::info('Calculated Signature', ['calcSignature' => $calcSignature]);

            if ($signature == $calcSignature) {
                Log::info("Callback valid untuk Order ID: $merchantOrderId, PaymentAmount: $PaymentAmount");

                $pembayaran = PembayaranDuitku::where('merchant_order_id', $merchantOrderId)->first();

                if ($pembayaran) {
                    try {
                        // Update status pembayaran menjadi 'success' dan simpan callback response
                        $pembayaran->update([
                            'status' => "Success",
                            'callback_response' => json_encode($request->all()),
                        ]);

                        // Update status Ppdb menjadi 2
                        $ppdb = Ppdb::where('merchant_order_id', $merchantOrderId)->first();
                        if ($ppdb) {
                            $ppdb->update(['status' => 2]);
                            Log::info("Ppdb status updated to 2 for Order ID: $merchantOrderId");

                            // Update status PembayaranPpdb menjadi 1
                            $pembayaranPpdb = PembayaranPpdb::where('merchant_order_id', $merchantOrderId)->first();
                            if ($pembayaranPpdb) {
                                $pembayaranPpdb->update(['status' => 1]);
                                Log::info("PembayaranPpdb status updated to 1 for Order ID: $merchantOrderId");
                            } else {
                                Log::error("PembayaranPpdb record not found for Order ID: $merchantOrderId");
                                return response()->json(['error' => 'PembayaranPpdb record not found'], 404);
                            }

                        } else {
                            Log::error("Ppdb record not found for Order ID: $merchantOrderId");
                            return response()->json(['error' => 'Ppdb record not found'], 404);
                        }

                    } catch (\Exception $e) {
                        Log::error("Error updating payment, Ppdb, or PembayaranPpdb record: " . $e->getMessage());
                        return response()->json(['error' => 'Update Error', 'message' => $e->getMessage()], 500);
                    }
                } else {
                    Log::error("Payment record not found for Order ID: $merchantOrderId");
                    return response()->json(['error' => 'Payment record not found'], 404);
                }

                return response()->json(['message' => 'Callback processed successfully', 'merchantOrderId' => $merchantOrderId], 200);
            } else {
                Log::error("Bad signature for Order ID: $merchantOrderId");
                return response()->json(['error' => 'Bad signature'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Unexpected error in handleCallback', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Unexpected Error', 'message' => $e->getMessage()], 500);
        }
    }


}
