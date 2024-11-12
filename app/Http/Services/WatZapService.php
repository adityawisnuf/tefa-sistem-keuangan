<?php

namespace App\Http\Services;

class WatZapService
{
    protected $apiUrl = 'https://api.watzap.id/v1/send_message';
    protected $apiKey;
    protected $numberKey;

    public function __construct()
    {
        // Store the API key and number key in the .env file
        $this->apiKey = env('WATZAP_API_KEY');
        $this->numberKey = env('WATZAP_NUMBER_KEY');
    }

    /**
     * Send WhatsApp message using cURL
     *
     * @param string $phoneNumber
     * @param string $message
     * @return mixed
     */
    public function sendMessage(string $phoneNumber, string $message)
    {
        $data = [
            'api_key' => $this->apiKey,
            'number_key' => $this->numberKey,
            'phone_no' => $phoneNumber,
            'message' => $message,
            'wait_until_send' => 1, // Optional parameter
        ];

        return $this->sendCurlRequest($this->apiUrl, $data);
    }

    /**
     * Send a POST request using cURL
     *
     * @param string $url
     * @param array $data
     * @return mixed
     */
    private function sendCurlRequest(string $url, array $data)
    {
        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        // Execute cURL request and store the response
        $response = curl_exec($ch);

        // Check for cURL errors   
        if(curl_errno($ch)) {
            return ['error' => curl_error($ch)];
        }

        // Close cURL session
        curl_close($ch);

        // Return the response from the API
        return json_decode($response, true);
    }
}