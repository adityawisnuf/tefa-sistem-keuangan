<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        // Validasi input
        $validate = $request->validate([
            'diajukan' => ['date', 'nullable'],
            'disetujui' => ['date', 'nullable'],
        ]);

        $query = Pengeluaran::with('anggaran', 'pengeluaran_kategori')->latest();

        // Filter berdasarkan tanggal diajukan
        if ($request->filled('diajukan')) {
            $query->whereDate('diajukan_pada', $request->diajukan);
        }

        // Filter berdasarkan tanggal disetujui
        if ($request->filled('disetujui')) {
            $query->whereDate('disetujui_pada', $request->disetujui);
        }

        // Paginate hasil
        $allPengeluaran = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data pengeluaran',
            'data' => $allPengeluaran
        ]);
    }

    public function show($id)
    {
        $pengeluaran = Pengeluaran::with('anggaran', 'pengeluaran_kategori')->find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengeluaran tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data pengeluaran',
            'data' => $pengeluaran
        ]);
    }
}
