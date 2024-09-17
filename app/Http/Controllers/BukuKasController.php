<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;
use App\Http\Resources\PembayaranResource; // Pastikan resource ini ada jika digunakan

class BukuKasController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter pencarian dari query string, jika ada
        $search = $request->query('search', '');

        // Mulai query untuk mengambil data dengan relasi
        $query = PembayaranSiswa::with('pembayaran_kategori', 'pembayaran', 'siswa')->latest();

        // Jika ada parameter pencarian, tambahkan kondisi pencarian
        if ($search) {
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%$search%");
            })
            ->orWhereHas('pembayaran', function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%$search%");
            });
        }

        // Paginate hasil query
        $allPayment = $query->paginate(10);

        // Mengembalikan response JSON dengan status dan data
        return response()->json([
            'status' => 200,
            'message' => 'Berhasil mendapatkan seluruh data kas',
            'resource' => $allPayment
        ]);
    }
}
