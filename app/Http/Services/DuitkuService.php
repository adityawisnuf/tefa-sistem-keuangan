<?php

namespace App\Http\Services;
use App\Models\PembayaranDuitku;
use App\Models\Siswa;
use App\Models\SiswaWalletRiwayat;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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
                    'error' => null,
                ],
                'statusCode' => $httpCode
            ];
        }

        $request = json_decode($request);
        $error_message = "Server Error {$httpCode} {$request->Message}";
        return [
            'data' => [
                'result' => null,
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
        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $customerVaName = $firstName . ' ' . $lastName;
        $callbackUrl = 'https://3c0a-2001-448a-3020-35f0-fc97-c058-1cbe-8881.ngrok-free.app/api/duitku/callback';
        $returnUrl = 'https://3c0a-2001-448a-3020-35f0-fc97-c058-1cbe-8881.ngrok-free.app/return';
        $expiryPeriod = 10;
        $signature = md5($this->merchantCode . $merchantOrderId . $paymentAmount . $this->apiKey);

        $alamat = $data['alamat'];
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
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
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
            PembayaranDuitku::create([
                'merchant_order_id' => $merchantOrderId,
                'reference' => $result['reference'],
                'payment_method' => $paymentMethod,
                'transaction_response' => $request
            ]);
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

    public function verifySignature(array $data)
    {
        $merchantCode = $data['merchantCode'];
        $amount = $data['amount'];
        $merchantOrderId = $data['merchantOrderId'];
        $signature = $data['signature'];

        if (!empty($merchantCode) && !empty($amount) && !empty($merchantOrderId) && !empty($signature)) {
            $calcSignature = md5($merchantCode . $amount . $merchantOrderId . $this->apiKey);
            if ($signature == $calcSignature) {
                Log::error('Success Callback');
                return true;
            }
            Log::error('Bad Signature Callback');
        } else {
            Log::error('Bad Parameter Callback');
        }

        return false;
    }
}