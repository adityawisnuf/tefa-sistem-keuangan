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
            'pendaftarDokumen',
            'pendaftaranAkademik',
            ]
        );
        
        $total_pendaftar = Ppdb::count();
        
        // Filter by name
        if ($request->filled('nama')) {
            $query->whereHas('pendaftar', function ($q) use ($request) {
                $nama = $request->input('nama');
                $q->where(function ($q) use ($nama) {
                    $q->where('nama_depan', 'like', '%' . $nama . '%')
                    ->orWhere('nama_belakang', 'like', '%' . $nama . '%');
                });
            });
        }
        
        // Filter by NIK
        if ($request->filled('nik')) {
            $query->whereHas('pendaftar', function ($q) use ($request) {
                $q->where('nik', 'like', '%' . $request->input('nik') . '%');
            });
        }
        
        // Filter by desired major
        if ($request->filled('jurusan_tujuan')) {
            $query->whereHas('pendaftaranAkademik', function ($q) use ($request) {
                $q->where('jurusan_tujuan', 'like', '%' . $request->input('jurusan_tujuan') . '%');
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $status);
        }
        
        // Filter by year range
        if ($tahunAwal && $tahunAkhir) {
            $query->whereYear('created_at', '>=', $tahunAwal)
            ->whereYear('created_at', '<=', $tahunAkhir);
        } elseif ($tahunAwal) {
            $query->whereYear('created_at', '>=', $tahunAwal);
        }
        
        
        // Paginate the results
        $ppdbs = $query->paginate(5);
        
        // Kembalikan hasil query
        return response()->json([
            'data' => $ppdbs,
            'total_pendaftar' => $total_pendaftar
        ], 200);
    }
    
}
