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
    $query = Ppdb::with([
        'pendaftar',
        'pendaftaranAkademik',
        'pendaftarDokumen',
    ]);

    // Apply filters based on query parameters
    if ($request->has('nama')) {
        $query->whereHas('pendaftar', function ($q) use ($request) {
            $q->where('nama_depan', 'like', '%' . $request->input('nama') . '%')
              ->orWhere('nama_belakang', 'like', '%' . $request->input('nama') . '%');
        });
    }

    if ($request->has('nik')) {
        $query->whereHas('pendaftar', function ($q) use ($request) {
            $q->where('nik', 'like', '%' . $request->input('nik') . '%');
        });
    }

    if ($request->has('jurusan_tujuan')) {
        $query->whereHas('pendaftaranAkademik', function ($q) use ($request) {
            $q->where('jurusan_tujuan', 'like', '%' . $request->input('jurusan_tujuan') . '%');
        });
    }

    if ($request->has('status')) {
        $query->where('status', $request->input('status'));
    }
    if ($request->has('tahun_awal')) {
        $query->whereYear('ppdb.created_at', '=', $request->input('tahun_awal'));
    }
    if ($request->has('tahun_akhir')) {
        $query->whereYear('ppdb.created_at', '<=', $request->input('tahun_akhir'));
    }

    if ($request->has('tahun_ajaran')) {
        $query->whereRaw('
            CONCAT(
                IF(MONTH(ppdb.created_at) >= 7, YEAR(ppdb.created_at), YEAR(ppdb.created_at) - 1),
                "/",
                IF(MONTH(ppdb.created_at) >= 7, YEAR(ppdb.created_at) + 1, YEAR(ppdb.created_at))
            ) like ?
        ', ['%' . $request->input('tahun_ajaran') . '%']);
    }

    // Paginate the results
    $ppdbs = $query->paginate(5);

    return response()->json([
        'data' => $ppdbs
    ]);
}

}
