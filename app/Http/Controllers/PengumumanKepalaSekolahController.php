<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengumuman;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PengumumanKepalaSekolahController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkrole:KepalaSekolah');
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

    public function submittedAnnouncements(): JsonResponse
    {
        $pengumuman = Pengumuman::where('status', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'pengumuman yang diajukan berhasil ditampilkan',
            'pengumuman' => $pengumuman
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'isi' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'gagal menambahkan pengumuman baru',
                'error' => $validator->errors()
            ], 422);
        }

        $pengumuman = Pengumuman::create([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'status' => 2
        ]);

        return response()->json([
            'success' => true,
            'message' => 'berhasil menambahkan pengumuman baru',
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

    public function update(Request $request, string $id): JsonResponse
    {
        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'pengumuman gagal ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'isi' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'gagal mengupdate pengumuman',
                'error' => $validator->errors()
            ], 422);
        }

        $pengumuman->update([
            'judul' => $request->judul,
            'isi' => $request->isi
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengumuman berhasil diupdate',
            'pengumuman' => $pengumuman
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'pengumuman gagal ditemukan'
            ], 404);
        }

        $pengumuman->delete();

        return response()->json([
            'success' => true,
            'message' => 'pengumuman berhasil dihapus'
        ], 200);
    }

    public function approve(string $id): JsonResponse
    {
        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'pengumuman gagal ditemukan'
            ], 404);
        }

        $pengumuman->update([
            'status' => 2
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengumuman berhasil disetujui'
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'pengumuman gagal ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'pesan_ditolak' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'masukkan alasan pengumuman ditolak',
                'error' => $validator->errors()
            ], 422);
        }

        $pengumuman->update([
            'status' => 3,
            'pesan_ditolak' => $request->pesan_ditolak
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengumuman berhasil ditolak'
        ]);
    }
}
