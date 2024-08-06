<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
class PembayaranController extends Controller
{
    public function getPaymentMethod()
    {
        $merchantCode = "DS19869";
        $apiKey = "8093b2c02b8750e4e73845f307325566";
        $datetime = now()->format('Y-m-d H:i:s');
        $paymentAmount = 10000;
        $signature = hash('sha256', $merchantCode . $paymentAmount . $datetime . $apiKey);

        $params = [
            'merchantcode' => $merchantCode,
            'amount' => $paymentAmount,
            'datetime' => $datetime,
            'signature' => $signature,
        ];

        $client = new Client();
        $url = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';

        try {
            $response = $client->post($url, [
                'json' => $params,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'verify' => false, // Menonaktifkan SSL verification, sebaiknya diaktifkan di production
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode == 200) {
                return response()->json($body);
            } else {
                return response()->json(['error' => 'Server error: ' . $statusCode], $statusCode);
            }
        } catch (\Exception $e) {
            Log::error('Error in getPaymentMethod: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}

