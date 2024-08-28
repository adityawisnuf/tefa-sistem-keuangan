<?php

namespace App\Http\Services;

class Duitku
{
    protected $merchantCode;
    protected $apiKey;
    protected $apiURL;
    protected $dateNow;

    public function __construct()
    {
        $this->merchantCode = env("DUITKU_MERCHANT_ID", "");
        $this->apiKey = env("DUITKU_API_KEY", "");
        $this->apiURL = env("APP_ENV", 'production') === 'production' ? 'https://passport.duitku.com' : 'https://sandbox.duitku.com';
        $this->dateNow =  date('Y-m-d H:i:s');
    }

    public function getPaymentMethod($paymentAmount = 0)
    {
        $signature = hash('sha256', $this->merchantCode . $paymentAmount . $this->dateNow . $this->apiKey);

        $params = array(
            'merchantcode' => $this->merchantCode,
            'amount' => $paymentAmount,
            'dateNow' => $this->dateNow,
            'signature' => $signature
        );

        $params_string = json_encode($params);



        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiURL . "/webapi/api/merchant/paymentmethod/getpaymentmethod");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params_string)
            )
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        //execute post
        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            $results = json_decode($request, true);
            print_r($results, false);
        } else {
            $request = json_decode($request);
            $error_message = "Server Error " . $httpCode . " " . $request->Message;
            echo $error_message;
        }
    }

    public function requestTransaction($data)
    {
        /*
        $data = [

        ]
        */
        $merchantOrderId = time() . ''; // dari merchant, unik
        $productDetails = 'Tes pembayaran menggunakan Duitku';
        $email = $data['user']['email']; // email pelanggan anda
        $phoneNumber = ''; // nomor telepon pelanggan anda (opsional)
        $additionalParam = ''; // opsional
        $merchantUserInfo = ''; // opsional
        $customerVaName = $data['user']['name']; // tampilan nama pada tampilan konfirmasi bank
        $callbackUrl = route("duitku.callback"); // url untuk callback
        $returnUrl = $data['return_url']; // url untuk redirect
        $expiryPeriod = 10; // atur waktu kadaluarsa dalam hitungan menit
        $signature = md5($this->merchantCode . $merchantOrderId . $data['payment_amount'] . $this->apiKey);

        // Customer Detail
        $nameArray = explode(" ", $data['user']['name']);
        $firstName = $nameArray[0];
        $lastName = $nameArray[1] ?? '';

        // Account Link
        //$accountLink = ''; // opsional
        //$creditCardDetail = '';

        // Address
        $alamat = "Jl. Kembangan Raya";
        $city = "Jakarta";
        $postalCode = "11530";
        $countryCode = "ID";

        $address = array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'address' => $alamat,
            'city' => $city,
            'postalCode' => $postalCode,
            'phone' => $phoneNumber,
            'countryCode' => $countryCode
        );

        $customerDetail = array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'billingAddress' => $address,
            'shippingAddress' => $address
        );

        $item1 = array(
            'name' => 'Test Item 1',
            'price' => 10000,
            'quantity' => 1
        );

        $item2 = array(
            'name' => 'Test Item 2',
            'price' => 30000,
            'quantity' => 3
        );

        $itemDetails = array(
            $item1,
            $item2
        );
        $params = array(
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $data['payment_amount'],
            'paymentMethod' => $data['payment_method'],
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => $additionalParam,
            'merchantUserInfo' => $merchantUserInfo,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            //'accountLink' => $accountLink,
            //'creditCardDetail' => $creditCardDetail,
            'itemDetails' => $itemDetails,
            'customerDetail' => $customerDetail,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod
        );

        $params_string = json_encode($params);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiURL . "webapi/api/merchant/v2/inquiry");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params_string)
            )
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        //execute post
        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            $result = json_decode($request, true);
            //header('location: '. $result['paymentUrl']);
            echo "paymentUrl :" . $result['paymentUrl'] . "<br />";
            echo "merchantCode :" . $result['merchantCode'] . "<br />";
            echo "reference :" . $result['reference'] . "<br />";
            echo "vaNumber :" . $result['vaNumber'] . "<br />";
            echo "amount :" . $result['amount'] . "<br />";
            echo "statusCode :" . $result['statusCode'] . "<br />";
            echo "statusMessage :" . $result['statusMessage'] . "<br />";
        } else {
            $request = json_decode($request);
            $error_message = "Server Error " . $httpCode . " " . $request->Message;
            echo $error_message;
        }
    }
}
