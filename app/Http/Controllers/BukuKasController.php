<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;

class BukuKasController extends Controller
{
    public function index()
    {
        // Mengambil data pembayaran dengan relasi 'pembayaran_kategori' dan 'pembayaran'
        $allPayment = PembayaranSiswa::with('pembayaran_kategori', 'pembayaran', 'siswa')->paginate(10);

        // Mengembalikan response JSON dengan status dan data
        return response()->json([
            'status' => 200,
            'message' => 'Berhasil mendapatkan seluruh data kas',
            'resource' => $allPayment 
        ]);
    }
}