<?php

namespace App\Http\Services;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class Duitku
{
    protected $merchantCode;

    protected $apiKey;

    protected $apiURL;

    protected $dateNow;

    protected $ch;

    public function __construct()
    {
        $this->merchantCode = env('DUITKU_MERCHANT_ID', '');
        $this->apiKey = env('DUITKU_API_KEY', '');
        $this->apiURL = env('APP_ENV', 'production') === 'production' ? 'https://passport.duitku.com' : 'https://sandbox.duitku.com';
        $this->dateNow = date('Y-m-d H:i:s');
        $this->ch = curl_init();
    }

    protected function executeCurlRequest($url, $params)
    {
        $params_string = json_encode($params);

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $this->ch,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length' => strlen($params_string),
            ]
        );
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($httpCode != 200) {
            $error_message = 'Server Error ' . $httpCode . ' ' . json_decode($response)->Message;
            Log::error($error_message);
            throw new HttpResponseException(response()->json(['error' => $error_message], $httpCode));
        }

        return json_decode($response, true);
    }

    public function getPaymentMethod($paymentAmount)
    {
        if (! $paymentAmount) {
            throw new HttpResponseException(response()->json(['error' => 'Payment amount cannot be null'], 402));
        }

        $signature = hash('sha256', $this->merchantCode . $paymentAmount . $this->dateNow . $this->apiKey);
        $params = [
            'merchantcode' => $this->merchantCode,
            'amount' => $paymentAmount,
            'datetime' => $this->dateNow,
            'signature' => $signature,
        ];

        return $this->executeCurlRequest($this->apiURL . '/webapi/api/merchant/paymentmethod/getpaymentmethod', $params);
    }

    public function requestTransaction($data, $expiryPeriod = 60)
    {
        $string = $this->merchantCode . $data['merchantOrderId'] . $data['payment_amount'] . $this->apiKey;
        $signature = md5($string);

        $params = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $data['payment_amount'],
            'paymentMethod' => $data['payment_method'],
            'merchantOrderId' => $data['merchantOrderId'],
            'productDetails' => $data['title'],
            'additionalParam' => '',
            'merchantUserInfo' => '',
            'customerVaName' => $data['user']['name'],
            'email' => $data['user']['email'],
            'phoneNumber' => $data['user']['phone'],
            'itemDetails' => $data['item_details'],
            'customerDetail' => [
                'firstName' => explode(' ', $data['user']['name'])[0],
                'lastName' => explode(' ', $data['user']['name'])[1] ?? '',
                'email' => $data['user']['email'],
                'phoneNumber' => $data['user']['phone'],
            ],
            'callbackUrl' => route('payment.transaction.callback'),
            'returnUrl' => $data['return_url'],
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod,
        ];
        Log::debug('requestTransaction: ', [$signature, $params, $string]);
        return $this->executeCurlRequest($this->apiURL . '/webapi/api/merchant/v2/inquiry', $params);
    }

    public function callback($data)
    {
        $signature = md5($this->merchantCode . $data['amount'] . $data['merchantOrderId'] . $this->apiKey);

        if ($signature !== $data['signature']) {
            Log::warning('Invalid callback signature');

            return false;
        }

        // Process the callback
        return $data['resultCode'] === '00';
    }
}
