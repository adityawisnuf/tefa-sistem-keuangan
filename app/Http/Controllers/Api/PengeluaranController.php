<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $validate = $request->validate([
            'diajukan' => ['date', 'nullable'],
            'disetujui' => ['date', 'nullable'],
        ]);

        // Query untuk mendapatkan data pengeluaran
        $query = Pengeluaran::with('anggaran', 'pengeluaran_kategori')->latest();

        // Filter berdasarkan tanggal diajukan
        if ($request->filled('diajukan')) {
            $query->whereDate('diajukan_pada', $request->diajukan);
        }

        // Filter berdasarkan tanggal disetujui
        if ($request->filled('disetujui')) {
            $query->whereDate('disetujui_pada', $request->disetujui);
        }

        $allPengeluaran = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data pengeluaran',
            'data' => $allPengeluaran,
        ]);
    }


    public function report(Request $request)
    {
        $query = Pengeluaran::with('anggaran', 'pengeluaran_kategori')->latest();
        if ($request->filled('diajukan')) {
            $query->whereDate('diajukan_pada', $request->diajukan);
        }
        if ($request->filled('disetujui')) {
            $query->whereDate('disetujui_pada', $request->disetujui);
        }
    
        // Ambil semua pengeluaran yang sudah terfilter
        $allPengeluaran = $query->get();
        $fileName = "Rekap_Pengeluaran.pdf";
    
       
        $data = ['pengeluarans' => $allPengeluaran, 'sekolah' => Sekolah::first()];
        $pdf = Pdf::loadView('print.pengeluaran', $data);
    
        return $pdf->stream($fileName);
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
            'data' => $pengeluaran,
        ]);
    }
}
