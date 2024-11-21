<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Sekolah;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        // Validasi input dari pengguna
        $request->validate([
            'nama_siswa' => ['nullable', 'integer'],
            'kelas' => ['nullable', 'integer'],
            'jurusan' => ['nullable', 'string'],
        ]);

        // Query data siswa dengan relasi
        $query = Siswa::with(['kelas', 'orangtua'])->oldest();

        // Filter berdasarkan input
        if ($request->filled('nama_siswa')) {
            // Memfilter berdasarkan nama siswa
            $query->where(function ($q) use ($request) {
                $q->where('nama_depan', 'like', "%{$request->nama_siswa}%")
                  ->orWhere('nama_belakang', 'like', "%{$request->nama_siswa}%")
                  ->orWhere('id', $request->nama_siswa); 
            });
        }

        if ($request->filled('kelas')) {
            // Memfilter berdasarkan kelas
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas); 
            });
        }

        if ($request->filled('jurusan')) {
            // Memfilter berdasarkan jurusan
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('jurusan', $request->jurusan);
            });
        }

        $siswaData = $query->paginate(10);

        $allData = collect($siswaData->items())->map(function ($siswa) {
            return [
                'nama_siswa' => $siswa->nama_depan . ' ' . $siswa->nama_belakang,
                'kelas' => $siswa->kelas->kelas ?? null,
                'jurusan' => $siswa->kelas->jurusan ?? null,
                'telepon' => $siswa->telepon,
                'orang_tua' => $siswa->orangtua->nama ?? null,
            ];
        });

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Berhasil mendapatkan data siswa',
        //     'data' => $allData,
        //     'pagination' => [
        //         'total' => $siswaData->total(),
        //         'current_page' => $siswaData->currentPage(),
        //         'last_page' => $siswaData->lastPage(),
        //         'per_page' => $siswaData->perPage(),
        //     ]
        // ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data siswa',
            'data' => $allData,
            'pagination' => [
                'total' => $siswaData->total(),
                'current_page' => $siswaData->currentPage(),
                'last_page' => $siswaData->lastPage(),
                'per_page' => $siswaData->perPage(),
                'next_page_url' => $siswaData->nextPageUrl(),
                'prev_page_url' => $siswaData->previousPageUrl(),
                'from' => $siswaData->firstItem(),
                'to' => $siswaData->lastItem(),
            ]
        ]);
    }  
        
    public function report(Request $request)
    {
        // Ambil parameter filter untuk siswa
        $query = Siswa::with(['kelas', 'orangtua', 'pembayaran', 'pembayaran_kategori'])->oldest();
    
        if ($request->filled('nama_siswa')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_depan', 'like', "%{$request->nama_siswa}%")
                  ->orWhere('nama_belakang', 'like', "%{$request->nama_siswa}%");
            });
        }
    
        if ($request->filled('kelas')) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas);
            });
        }
    
        if ($request->filled('jurusan')) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('id', $request->jurusan);
            });
        }
    
        // Ambil data siswa yang sesuai filter
        $siswas = $query->get();
    
        // Loop untuk mengumpulkan data pembayaran terkait
        $result = [];
        foreach ($siswas as $siswa) {
            // Ambil data pembayaran untuk setiap siswa berdasarkan tipe pembayaran yang aktif
            $pembayaranList = Pembayaran::where('siswa_id', $siswa->id)
                ->where('status', 1) // Hanya pembayaran yang aktif
                ->with('pembayaran_kategori', 'siswa.orangtua') // Include relasi siswa dan orangtua
                ->get();
    
            $payments = [];
            $totalTagihan = 0;
    
            foreach ($pembayaranList as $pembayaran) {
                $nominal = $pembayaran->nominal;
                $status = ($pembayaran->status == 1) ? 'Lunas' : 'Belum Lunas';
    
                if ($status == 'Belum Lunas') {
                    $totalTagihan += $nominal;
                }
    
                $payments[] = [
                    'pembayaran_ke' => $pembayaran->pembayaran_ke,
                    'nominal' => $nominal,
                    'status' => $status,
                    'orangtua' => $pembayaran->siswa->orangtua->nama ?? "Tidak Diketahui", // Nama orangtua
                ];
            }
    
            // Masukkan data siswa dan daftar pembayaran ke dalam hasil akhir
            $result[] = [
                'nama_siswa' => $siswa->nama_depan . ' ' . $siswa->nama_belakang,
                'kelas' => $siswa->kelas->kelas ?? null,
                'jurusan' => $siswa->kelas->jurusan ?? null,
                'telepon' => $siswa->telepon,
                'orangtua' => $siswa->orangtua->nama ?? "Tidak Diketahui",
                'sisa_tagihan' => $totalTagihan,
                'payments' => $payments,
            ];
        }
    
        // Konfigurasi untuk export PDF
        $fileName = "Rekap_Pembayaran.pdf";
        $data = [
            'pembayarans' => $result,
            'sekolah' => Sekolah::first()
        ];
    
        // Generate PDF
        $pdf = Pdf::loadView('print.PrintPdfSPP', $data);
        return $pdf->stream($fileName);
    }
    

}
    
