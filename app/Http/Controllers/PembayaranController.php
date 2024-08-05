<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function payment()
    {
        $params = [
            'amount' => 10000, // Jumlah pembayaran dalam rupiah
            'order_id' => uniqid(), // ID pesanan unik
            'customer_name' => 'John Doe',
            // ... parameter lainnya sesuai dokumentasi Duitku
        ];

        $paymentUrl = $this->generatePaymentUrl($params);

        return redirect($paymentUrl);
    }
}
