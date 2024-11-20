<?php

namespace App\Http\Controllers;

use App\Http\Services\WatZapService as ServicesWatZapService;
use App\Models\PembayaranSiswa;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SendPaymentSuccesController extends Controller
{
    protected $watZapService;

    public function __construct(ServicesWatZapService $watZapService)
    {
        $this->watZapService = $watZapService;
    }

    /**
     * Send a message to the specified phone number
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'phone_no' => 'required|string',
            'message' => 'required|string',
        ]);

        // Call the service to send the WhatsApp message
        $response = $this->watZapService->sendMessage($request->phone_no, $request->message);

        // Return the response
        return response()->json([
            'status' => isset($response['error']) ? 'failed' : 'success',
            'data' => $response,
        ]);
    }

    public function generateReceiptPDF($order_id)
    {
        // Ambil data pembayaran berdasarkan order_id
        $pembayaran = PembayaranSiswa::where('merchant_order_id', $order_id)->firstOrFail();

        // Data untuk PDF
        $data = [
            'nama_sekolah' => $pembayaran->sekolah,
            'username' => $pembayaran->user_name,
            'payment_name' => $pembayaran->productDetail,
            'ds_code' => $pembayaran->reference,
            'merchant_order_id' => $pembayaran->merchant_order_id,
            'nominal' => $pembayaran->nominal,
            'payment_method' => $pembayaran->payment_method,
            'payment_time' => $pembayaran->updated_at,
            'payment_status' => $pembayaran->transactionState . ' ' . $pembayaran->transactionStateStatus,
    ];


    $pdf = Pdf::loadView('wa.payment_receipt', $data);

    $pdfPath = storage_path('app/public/struk_pembayaran/'.$pembayaran->merchantOrderId.'.pdf');
    $pdf->save($pdfPath);

    return asset('storage/struk_pembayaran/'.$pembayaran->merchantOrderId.'.pdf');
}
}

