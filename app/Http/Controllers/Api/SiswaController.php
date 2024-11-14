<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        // Validasi input dari pengguna
        $request->validate([
            'nama_siswa' => ['nullable', 'integer'],
            'kelas' => ['nullable', 'integer'],
            'jurusan' => ['nullable', 'integer'],
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
                $q->where('id', $request->jurusan);
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
}
