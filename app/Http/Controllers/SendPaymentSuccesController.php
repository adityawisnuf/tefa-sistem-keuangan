<?php

namespace App\Http\Controllers;

use App\Http\Services\WatZapService as ServicesWatZapService;
use App\Models\Pembayaran;
use App\Models\PembayaranSiswa;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function sendPaymentReminder()  
{
    // Ambil data pembayaran yang statusnya aktif dan jenisnya bulanan atau tahunan
    $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
        $query->whereIn('jenis_pembayaran', [1, 2]) // Pembayaran bulanan dan tahunan
              ->where('status', 1); // Hanya yang aktif
    })
    ->whereNull('deleted_at')
    ->get();

    // Ambil tanggal sekarang
    $currentDate = now();

    foreach ($pembayaranList as $pembayaran) {
        // Ambil data siswa
        $siswa = $pembayaran->siswa;
        $phoneNumber = $siswa->telepon;

        // Tentukan tanggal jatuh tempo
        if ($pembayaran->pembayaran_kategori->jenis_pembayaran == 1) {
            // Jenis pembayaran bulanan: Gunakan tanggal `created_at` dan hitung 3 hari setelahnya
            $createdDate = \Carbon\Carbon::parse($pembayaran->created_at);
            $dueDate = $createdDate->addDays(3);

            // Detail pembayaran untuk bulanan: tagihan ke dan nominal
            $paymentDetails = [
                'tagihanKe' => $pembayaran->pembayaran_ke,
                'nominal' => $pembayaran->nominal
            ];

        } else {
            // Jenis pembayaran tahunan: Gunakan tanggal pembayaran yang ada (tanggal bulan tertentu)
            $dueDate = \Carbon\Carbon::createFromFormat('d-m', $pembayaran->pembayaran_kategori->tanggal_pembayaran);
            // Tentukan tahun jatuh tempo (gunakan tahun sekarang jika belum lewat, jika sudah gunakan tahun depan)
            if ($dueDate->lt($currentDate)) {
                $dueDate->addYear();
            }

            // Detail pembayaran untuk tahunan: hanya nominal
            $paymentDetails = [
                'nominal' => $pembayaran->nominal
            ];
        }

        // Hitung berapa hari lagi jatuh tempo
        $daysUntilDue = $currentDate->diffInDays($dueDate);

        // Kirim pengingat jika jatuh tempo dalam 3 hari
        if ($daysUntilDue == 3) {
            // Mengirim pesan pengingat via WhatsApp dengan detail pembayaran
            $response = $this->watZapService->sendReminder(
                $phoneNumber, 
                $siswa->nama_depan, 
                $pembayaran->pembayaran_kategori->nama, 
                $dueDate->toFormattedDateString(),
                $paymentDetails
            );

            // Log pengiriman
            if (isset($response['error'])) {
                Log::error('Gagal mengirim WhatsApp: ' . $response['error']);
            } else {
                Log::info('Pesan WhatsApp berhasil terkirim ke: ' . $phoneNumber);
            }
        }
    }

    return response()->json(['success' => true, 'message' => 'Pengingat pembayaran telah dikirim.']);
}
}

