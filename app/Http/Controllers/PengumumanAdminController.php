<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PengumumanAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkrole:Admin,Bendahara');
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
        $pengumuman = Pengumuman::where('user_id', Auth::user()->id)->where('status', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'pengumuman yang diajukan berhasil ditampilkan',
            'pengumuman' => $pengumuman
        ], 200);
    }

    public function rejectedAnnouncements(): JsonResponse
    {
        $pengumuman = Pengumuman::where('user_id', Auth::user()->id)->where('status', 3)->get();

        return response()->json([
            'success' => true,
            'message' => 'pengumuman yang ditolak berhasil ditampilkan',
            'pengumuman' => $pengumuman
        ]);
    }

    public function store(Request $request)
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
            'status' => 1,
            'user_id' => Auth::user()->id
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

        if ($pengumuman->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengupdate pengumuman ini'
            ], 403);
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

        if ($pengumuman->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus pengumuman ini'
            ], 403);
        }

        $pengumuman->delete();

        return response()->json([
            'success' => true,
            'message' => 'pengumuman berhasil dihapus'
        ], 200);
    }
}
