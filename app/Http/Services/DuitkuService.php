<?php

namespace App\Http\Services;

class DuitkuService
{
    protected $merchantCode;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->merchantCode = env('DUITKU_MERCHANT_CODE');
        $this->apiKey = env('DUITKU_API_KEY');
        $this->baseUrl = env('DUITKU_ENV') === 'sandbox' ? 'https://sandbox.duitku.com/webapi' : 'https://duitku.com/webapi';
    }

    public function getPaymentMethod()
    {
        $paymentAmount = 0;
        $datetime = date('Y-m-d H:i:s');
        $signatureString = $this->merchantCode . $paymentAmount . $datetime . $this->apiKey; // Gabungkan string
        $signature = hash('sha256', $signatureString);
        
        $params = [
            'merchantcode' => $this->merchantCode,
            'amount' => $paymentAmount,
            'datetime' => $datetime,
            'signature' => $signature
        ];

        $params_string = json_encode($params);

        $url = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($params_string)]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            $results = json_decode($request, true);
            return [
                'data' => [
                    'result' => $results,
                    'error' => '',
                ],
                'statusCode' => $httpCode
            ];
        }

        $request = json_decode($request);
        $error_message = "Server Error {$httpCode} {$request->Message}";
        return [
            'data' => [
                'result' => [],
                'error' => $error_message,
            ],
            'statusCode' => $httpCode
        ];
    }

    public function requestTransaction(array $data)
    {
        $paymentAmount = $data['paymentAmount'];
        $paymentMethod = $data['paymentMethod'];
        $merchantOrderId = time() . '';
        $productDetails = 'Tes pembayaran menggunakan Duitku';
        $email = $data['email'];
        $phoneNumber = $data['phoneNumber'];
        $additionalParam = ''; // opsional
        $merchantUserInfo = ''; // opsional
        $customerVaName = $data['customerVaName']; // tampilan nama pada tampilan konfirmasi bank
        $callbackUrl = 'https://ef7a-180-244-135-171.ngrok-free.app/api/duitku/callback'; // url untuk callback
        $returnUrl = 'https://ef7a-180-244-135-171.ngrok-free.app/return'; // url untuk redirect
        $expiryPeriod = 10; // atur waktu kadaluarsa dalam hitungan menit
        $signature = md5($this->merchantCode . $merchantOrderId . $paymentAmount . $this->apiKey);

        [$firstName, $lastName] = explode(' ', $customerVaName, 2);

        $alamat = "Jl. Kembangan Raya";
        $city = "Jakarta";
        $postalCode = "11530";
        $countryCode = "ID";

        $address = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'address' => $alamat,
            'city' => $city,
            'postalCode' => $postalCode,
            'phone' => $phoneNumber,
            'countryCode' => $countryCode
        ];

        $customerDetail = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'billingAddress' => $address,
            'shippingAddress' => $address
        ];

        $params = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => $additionalParam,
            'merchantUserInfo' => $merchantUserInfo,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            //'accountLink' => $accountLink,
            //'creditCardDetail' => $creditCardDetail,
            'itemDetails' => $data['itemDetails'],
            'customerDetail' => $customerDetail,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod
        ];

        $params_string = json_encode($params);
        $url = 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'; // Sandbox
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params_string)
            ]
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ($httpCode == 200) {
            $result = json_decode($request, true);
            return [
                'data' => [
                    'result' => $result,
                    'error' => '',
                ],
                'statusCode' => $httpCode
            ];
        }

        $request = json_decode($request);
        $error_message = "Server Error {$httpCode} {$request->Message}";
        return [
            'data' => [
                'result' => [],
                'error' => $error_message,
            ],
            'statusCode' => $httpCode
        ];
    }

    public function callback(array $data)
    {
        $merchantCode = $data['merchantCode'] ?? null;
        $amount = $data['amount'] ?? null;
        $merchantOrderId = $data['merchantOrderId'] ?? null;
        $signature = $data['signature'] ?? null;

        file_put_contents('callback.txt', "* Callback *\r\n", FILE_APPEND | LOCK_EX);
        file_put_contents('callback.txt', "* {$amount} *\r\n\r\n", FILE_APPEND | LOCK_EX);

        if (!empty($merchantCode) && !empty($amount) && !empty($merchantOrderId) && !empty($signature)) {
            $calcSignature = md5($merchantCode . $amount . $merchantOrderId . $this->apiKey);

            if ($signature == $calcSignature) {
                file_put_contents('callback.txt', "* Success *\r\n\r\n", FILE_APPEND | LOCK_EX);

            } else {
                file_put_contents('callback.txt', "* Bad Signature *\r\n\r\n", FILE_APPEND | LOCK_EX);
            }
        } else {
            file_put_contents('callback.txt', "* {$amount} *\r\n\r\n", FILE_APPEND | LOCK_EX);
        }
    }
}