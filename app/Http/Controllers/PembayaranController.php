<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;
use App\Http\Resources\PembayaranResource; // Pastikan resource ini ada jika digunakan

class PembayaranController extends Controller
{

    public function index(Request $request)
    {
        // Validate the request inputs
        $validate = $request->validate([
            'kode_transaksi' => ['string', 'nullable'],
            'siswa' => [ 'nullable'],
            'kelas' => [ 'nullable'],
            'jenis_pembayaran' => ['string', 'nullable'],
            'tanggal_pembayaran' => ['date', 'nullable'],
            'rekapitulasi' => ['string', 'nullable', 'in:Bulanan,Tahunan,Harian'],
        ]);


        // Start a query with relationships
        $query = PembayaranSiswa::with('pembayaran_kategori', 'pembayaran', 'siswa')
            ->latest();

        // Apply filters based on request data
        if ($request->filled('kode_transaksi')) {
            $query->where('merchant_order_id', $request->kode_transaksi);
        }

        if ($request->filled('siswa')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id', $request->siswa['value']);
            });
        }


        if ($request->filled('kelas')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas['value']);
            });
        }

        if ($request->filled('jenis_pembayaran')) {
            $query->whereHas('pembayaran.pembayaran_kategori', function ($q) use ($request) {
                $q->where('jenis_pembayaran', $request->jenis_pembayaran);
            });
        }

        if ($request->filled('tanggal_pembayaran')) {
            $query->whereDate('created_at', $request->tanggal_pembayaran);
        }

        // Rekapitulasi logic (monthly, yearly, daily summary if required)
        if ($request->rekapitulasi === 'bulanan') {
            $query->whereMonth('created_at', now()->month);
        } elseif ($request->rekapitulasi === 'tahunan') {
            $query->whereYear('created_at', now()->year);
        } elseif ($request->rekapitulasi === 'harian') {
            $query->whereDate('created_at', now());
        }


        // Paginate the result
        $allPayment = $query->with('siswa.kelas')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data kas',
            'data' => $allPayment
        ]);
    }
}
