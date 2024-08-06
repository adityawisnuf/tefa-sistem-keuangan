<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

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
        $merchantCode = $request->input('merchantCode');
        $apiKey = $request->input('apiKey');
        $paymentAmount = $request->input('paymentAmount');
        $paymentMethod = $request->input('paymentMethod');
        $email = $request->input('email');
        $customerVaName = $request->input('customerVaName');
        $callbackUrl = $request->input('callbackUrl');
        $returnUrl = $request->input('returnUrl');
        $expiryPeriod = $request->input('expiryPeriod');
        $signature = md5($merchantCode  . $paymentAmount . $apiKey);

        $address = [
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'postalCode' => $request->input('postalCode'),
            'countryCode' => $request->input('countryCode')
        ];

        $customerDetail = [
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'email' => $email,
            'billingAddress' => $address,
            'shippingAddress' => $address
        ];

        $itemDetails = $request->input('itemDetails'); // Assumed to be an array of item details

        $params = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'itemDetails' => $itemDetails,
            'customerDetail' => $customerDetail,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod
        ];

        $client = new Client();
        $response = $client->post('https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry', [
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
}
