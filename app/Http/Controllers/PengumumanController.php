<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\User;
use App\Notifications\NewPengumumanNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class PengumumanController extends Controller
{
    // semua pengumuman yang disetujui
    public function AllAnnouncements(): JsonResponse
    {
        if (Auth::user()->role === 'Kepala Sekolah') {
            $pengumuman = Pengumuman::whereIn('status', [2, 3])->get();

            return response()->json([
                'success' => true,
                'message' => 'pengumuman berhasil ditampilkan',
                'pengumuman' => $pengumuman
            ], 200);
        } else {
            $pengumuman = Pengumuman::where('status', 2)->get();

            return response()->json([
                'success' => true,
                'message' => 'pengumuman berhasil ditampilkan',
                'pengumuman' => $pengumuman
            ], 200);
        }
    }
  
    // pengumuman user (saat ini) yang disetujui
    public function approvedAnnouncements(): JsonResponse
    {
        $pengumuman = Pengumuman::where('user_id', Auth::user()->id)->where('status', 2)->get();

        return response()->json([
            'success' => true,
            'message' => 'pengumuman berhasil ditampilkan',
            'pengumuman' => $pengumuman
        ], 200);
    }

    // pengumuman yang diajukan
    public function submittedAnnouncements(): JsonResponse
    {
        // admin dan bendahara
        if (Auth::user()->role === 'Admin' || Auth::user()->role === 'Bendahara') {
            $pengumuman = Pengumuman::where('user_id', Auth::user()->id)->where('status', 1)->get();

            return response()->json([
                'success' => true,
                'message' => 'pengumuman yang diajukan berhasil ditampilkan',
                'pengumuman' => $pengumuman
            ], 200);
        } else {
            // kepala sekolah
            $pengumuman = Pengumuman::where('status', 1)->get();

            return response()->json([
                'success' => true,
                'message' => 'pengumuman yang diajukan berhasil ditampilkan',
                'pengumuman' => $pengumuman
            ], 200);
        }
    }

    // pengumuman user (saat ini) yang ditolak
    public function rejectedAnnouncements(): JsonResponse
    {
        $pengumuman = Pengumuman::where('user_id', Auth::user()->id)->where('status', 3)->get();

        return response()->json([
            'success' => true,
            'message' => 'pengumuman yang ditolak berhasil ditampilkan',
            'pengumuman' => $pengumuman
        ]);
    }

    // membuat pengumuman
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

        if (Auth::user()->role === "Kepala Sekolah") {
            $pengumuman = Pengumuman::create([
                'judul' => $request->judul,
                'isi' => $request->isi,
                'status' => 2,
                'user_id' => Auth::user()->id,
                'approved_at' => now()
            ]);
        } else {
            $pengumuman = Pengumuman::create([
                'judul' => $request->judul,
                'isi' => $request->isi,
                'status' => 1,
                'user_id' => Auth::user()->id
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'berhasil menambahkan pengumuman baru',
            'pengumuman' => $pengumuman
        ], 200);
    }

    // melihat detail pengumuman
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

    // update pengumuman
    public function update(Request $request, string $id): JsonResponse
    {
        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'pengumuman gagal ditemukan'
            ], 404);
        }

        if ($pengumuman->user_id === Auth::user()->id || Auth::user()->role === 'Kepala Sekolah') {
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
            
            if (Auth::user()->role === 'Kepala Sekolah') {
                $pengumuman->update([
                    'judul' => $request->judul,
                    'isi' => $request->isi
                ]);
            } else {
                $pengumuman->update([
                    'judul' => $request->judul,
                    'isi' => $request->isi,
                    'status' => 1,
                    'approved_at' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'pengumuman berhasil diupdate',
                'pengumuman' => $pengumuman
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk mengupdate pengumuman ini'
        ], 403);
    }

    // menghapus pengumuman
    public function destroy(string $id): JsonResponse
    {
        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'pengumuman gagal ditemukan'
            ], 404);
        }

        if ($pengumuman->user_id === Auth::id() || Auth::user()->role === 'Kepala Sekolah') {
            $pengumuman->delete();
        
            return response()->json([
                'success' => true,
                'message' => 'pengumuman berhasil dihapus'
            ], 200);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk menghapus pengumuman ini'
        ], 403);
    }

    // menyetujui pengumuman
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
            'status' => 2,
            'approved_at' => now(),
            'pesan_ditolak' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengumuman berhasil disetujui'
        ]);
    }

    // menolak pengumuman
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
