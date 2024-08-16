<?php

namespace App\Http\Controllers;

use App\Models\Ppdb;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TrackingPendaftaran extends Controller
{
    public function trackPendaftaran()
    {
        // Ambil user ID yang sedang login
        $userId = Auth::id();

        // Ambil semua data ppdb berdasarkan user ID
        $ppdbs = Ppdb::with([
            'pendaftaranAkademik',
            'pendaftar',
            'pendaftarDokumen',
        ])
        ->where('user_id', $userId)
        ->get(); // Mengambil semua data yang cocok dengan kriteria
    
        return response()->json($ppdbs);
    }
    
    public function getAllPendaftarans()
    {
        $ppdbs = Ppdb::with([
            'pendaftar',
            'pendaftaranAkademik', // Gunakan nama metode relasi yang benar
            'pendaftarDokumen', // Gunakan nama metode relasi yang benar
        ])->paginate(2);

        return response()->json([
            'data' => $ppdbs
        ]);
    }
}
