<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        // Query untuk mengambil data siswa tanpa filter
        $query = Siswa::with(['kelas', 'orangtua'])->oldest();

        // Ambil data siswa
        $siswaData = $request->input('page') === 'all' ? $query->get() : $query->paginate(10);

        // Format data yang diambil
        $formattedData = $siswaData->map(function ($siswa) {
            return [
                'nama_siswa' => $siswa->nama_depan.' '.$siswa->nama_belakang,
                'telepon' => $siswa->telepon,
                'kelas' => $siswa->kelas->kelas ?? null, // Mengambil nama kelas
                'jurusan' => $siswa->kelas->jurusan ?? null, // Mengambil nama jurusan
                'orangtua' => $siswa->orangtua->nama ?? null, // Mengambil nama orang tua
            ];
        });

        return response()->json([
            'message' => 'Berhasil mendapatkan data siswa',
            'success' => true,
            'data' => $formattedData,
        ]);
    }
}
