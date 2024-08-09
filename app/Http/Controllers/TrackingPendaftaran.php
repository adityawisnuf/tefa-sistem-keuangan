<?php

namespace App\Http\Controllers;

use App\Models\Ppdb;
use Illuminate\Http\Request;

class TrackingPendaftaran extends Controller
{
    public function trackPendaftaran($ppdbId)
    {
        $ppdb = Ppdb::with([
            'pendaftaran_akademik',
            'pendaftar',
            'pendaftar_dokumen',
        ])->findOrFail($ppdbId);
    
        return response()->json($ppdb);
    }
    public function getAllPendaftarans()
{
    $ppdbs = Ppdb::with([
        'pendaftaran_akademik',
        'pendaftar',
        'pendaftar_dokumen',
    ])->get();

    return response()->json($ppdbs);
}
}
