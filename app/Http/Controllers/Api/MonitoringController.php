<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anggaran;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Mengambil 7 data Anggaran terakhir berdasarkan tanggal pembuatan.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLastSevenAnggaran()
    {
        // Mengambil 7 data Anggaran terakhir dari database berdasarkan tanggal pembuatan
        $lastSeven = Anggaran::orderBy('created_at', 'desc')
                    ->take(7)
                    ->get();

        // Mengembalikan response dalam format JSON
        return response()->json([
            'status' => 'success',
            'data' => $lastSeven,
            'message' => 'Berhasil mengambil 7 data anggaran terbaru.'
        ]);
    }

    // Anda dapat menambahkan metode lain di controller ini jika diperlukan
}
