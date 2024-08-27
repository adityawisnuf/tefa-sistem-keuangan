<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengumumanOrangTuaController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkrole:OrangTua,Siswa');
    }

    public function approvedAnnouncements(): JsonResponse
    {
        $pengumuman = Pengumuman::where('status', 2)->get();

        return response()->json([
            'success' => true,
            'message' => 'pengumuman berhasil ditampilkan',
            'pengumuman' => $pengumuman
        ], 200);
    }

    public function show(string $id): JsonResponse
    {
        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'gagal menampilkan pengumuman'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'berhasil menampilkan pengumuman',
            'pengumuman' => $pengumuman
        ], 200);
    }
}
