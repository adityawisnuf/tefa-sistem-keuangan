<?php

namespace App\Http\Controllers;

use App\Models\Ppdb;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        ->get();

        // Format tanggal created_at dan tambahkan ke array
        $ppdb = $ppdbs->map(function($ppdbs) {
            $ppdbArray = $ppdbs->toArray();
            $ppdbArray['created_at'] = Carbon::parse($ppdbs->created_at)->format('d-m-Y');
            return $ppdbArray;
        });

        return response()->json($ppdb);
    }


    
    public function getAllPendaftarans()
    {
        $ppdbs = Ppdb::with([
            'pendaftar',
            'pendaftaranAkademik', // Gunakan nama metode relasi yang benar
            'pendaftarDokumen', // Gunakan nama metode relasi yang benar
        ])->paginate(5);

        return response()->json([
            'data' => $ppdbs
        ]);
    }
    public function searchPendaftarans(Request $request)
{
// Validasi input
$request->validate([
    'tahun_awal' => 'nullable|integer|min:2000',
    'tahun_akhir' => 'nullable|integer|min:2000',
    'status' => 'nullable|integer|in:1,2,3,4',
]);

// Ambil parameter dari request
$tahunAwal = $request->input('tahun_awal');
$tahunAkhir = $request->input('tahun_akhir');
$status = $request->input('status');

// Query untuk memfilter data
$query = Ppdb::with([
    'pendaftar',
    'pendaftaranAkademik', 
    'pendaftarDokumen',
]);

// Filter by name
if ($request->has('nama')) {
    $query->whereHas('pendaftar', function ($q) use ($request) {
        $q->where('nama_depan', 'like', '%' . $request->input('nama') . '%')
          ->orWhere('nama_belakang', 'like', '%' . $request->input('nama') . '%');
    });
}

// Filter by NIK
if ($request->has('nik')) {
    $query->whereHas('pendaftar', function ($q) use ($request) {
        $q->where('nik', 'like', '%' . $request->input('nik') . '%');
    });
}

// Filter by desired major
if ($request->has('jurusan_tujuan')) {
    $query->whereHas('pendaftaranAkademik', function ($q) use ($request) {
        $q->where('jurusan_tujuan', 'like', '%' . $request->input('jurusan_tujuan') . '%');
    });
}

// Filter by status
if ($status) {
    $query->where('status', $status);
}

// Filter by year range
if ($tahunAwal && $tahunAkhir) {
    $query->where(function($query) use ($tahunAwal, $tahunAkhir) {
        $query->whereYear('created_at', '>=', $tahunAwal)
              ->whereYear('created_at', '<=', $tahunAkhir);
    });
} elseif ($tahunAwal) {
    $query->whereYear('created_at', '>=', $tahunAwal);
}

// Paginate the results
$ppdbs = $query->paginate(5);

// Jika tidak ada data, kembalikan array kosong dengan status 200
if ($ppdbs->isEmpty()) {
    return response()->json([], 200);
}

// Kembalikan hasil query jika ada data
return response()->json([
    'data' => $ppdbs
], 200);

}

}
