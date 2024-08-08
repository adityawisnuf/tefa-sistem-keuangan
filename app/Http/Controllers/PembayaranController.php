<?php

namespace App\Http\Controllers;

use App\Models\PembayaranDuitku;
use App\Models\Pembayaran;
use App\Models\PembayaranKategori;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{
    // Method untuk membuat transaksi
    public function createTransaction(Request $request)
    {
        // Ambil data dari request
        $merchantCode = $request->input('merchantCode');
        $apiKey = $request->input('apiKey');
        $paymentAmount = $request->input('paymentAmount');
        $paymentMethod = $request->input('paymentMethod');
        $merchantOrderId = Str::uuid();
        $callbackUrl = $request->input('callbackUrl');
        $returnUrl = $request->input('returnUrl');
        $expiryPeriod = $request->input('expiryPeriod');
        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

        Log::info('Signature generated in createTransaction', ['signature' => $signature]);

        // Buat pembayaran kategori baru
        $pembayaranKategori = PembayaranKategori::create([
            'nama' => $request->input('nama_kategori'), // Tambahkan nama kategori dari request
            'jenis_pembayaran' => $request->input('jenis_pembayaran'), // Tambahkan jenis pembayaran dari request
            'tanggal_pembayaran' => now(), // Atur tanggal pembayaran ke saat ini
            'status' => 1 // 1 untuk aktif
        ]);

        $params = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
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

                PembayaranDuitku::create([
                    'merchant_order_id' => $merchantOrderId,
                    'reference' => $responseBody['reference'],
                    'payment_method' => $paymentMethod,
                    'transaction_response' => json_encode($responseBody),
                    'callback_response' => null,
                    'status' => 'pending',
                ]);

                Pembayaran::create([
                    'siswa_id' => null,
                    'pembayaran_kategori_id' => $pembayaranKategori->id, // Gunakan pembayaran_kategori_id yang diambil
                    'nominal' => $paymentAmount,
                    'status' => 1, // 1 untuk aktif
                    'kelas_id' => null,
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

    // Method untuk menangani callback
    public function handleCallback(Request $request)
    {
        $apiKey = '8093b2c02b8750e4e73845f307325566';
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
