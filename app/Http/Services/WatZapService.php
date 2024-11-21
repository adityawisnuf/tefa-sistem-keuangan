<?php

namespace App\Http\Services;

class WatZapService
{
    protected $apiUrl = 'https://api.watzap.id/v1/send_message';
    protected $apiKey;
    protected $numberKey;

    public function __construct()
    {
        $this->apiKey = env('WATZAP_API_KEY');
        $this->numberKey = env('WATZAP_NUMBER_KEY');
    }

    public function sendMessage(string $phoneNumber, string $message)
    {
        $data = [
            'api_key' => $this->apiKey,
            'number_key' => $this->numberKey,
            'phone_no' => $phoneNumber,
            'message' => $message,
            'wait_until_send' => 1,
        ];

        return $this->sendCurlRequest($this->apiUrl, $data);
    }

    private function sendCurlRequest(string $url, array $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    public function sendPaymentSuccessWhatsApp($phoneNumber, $schoolName, $userName, $downloadLink)
    {
        $message = "Terimakasih, $userName, telah melakukan pembayaran ke $schoolName. Klik link berikut untuk mengunduh struk pembayaran: $downloadLink";
    
        return $this->sendMessage($phoneNumber, $message);
    }
    
    public function sendReminder($phoneNumber, $userName, $paymentName, $dueDate, $paymentDetails = null)
{
    // Membuat pesan dengan format yang lebih kaya dan informasi tambahan
    if ($paymentDetails) {
        // Jika ada detail pembayaran (nominal dan tagihan ke)
        $message = "Halo, $userName 👋\nPembayaran $paymentName Anda untuk Tagihan ke-$paymentDetails[tagihanKe] akan jatuh tempo pada:\ntanggal: $dueDate\nNominal : Rp. " . number_format($paymentDetails['nominal'], 0, ',', '.') ."\nHarap melakukan pembayaran tepat waktu,\nTerima kasih !";
    } else {
        // Jika tidak ada detail pembayaran (misalnya pembayaran tahunan)
        $message = "Halo, $userName 👋\nPembayaran $paymentName Anda akan jatuh tempo pada tanggal $dueDate. Nominal: " . number_format($paymentDetails['nominal'], 0, ',', '.') . ". Harap melakukan pembayaran tepat waktu. Terima kasih!";
    }

    // Mengirim pesan via WhatsApp
    return $this->sendMessage($phoneNumber, $message);
}

}
