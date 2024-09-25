<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use App\Models\PembayaranKategori;
use Illuminate\Http\Request;

class PembayaranSiswaController extends Controller
{
    public function index()
    {
        $pembayaran_siswa = PembayaranSiswa::with('siswa', 'pembayaran_kategori')->get();
        return response()->json($pembayaran_siswa);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'pembayaran_id' => 'required|exists:pembayaran,id',
            'nominal' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $pembayaran_siswa = PembayaranSiswa::create($validatedData);

        return response()->json(['message' => 'Pembayaran berhasil ditambahkan', 'data' => $pembayaran_siswa]);
    }

    public function show($id)
    {
        $pembayaran_siswa = PembayaranSiswa::with('siswa', 'pembayaran_kategori')->findOrFail($id);
        return response()->json($pembayaran_siswa);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nominal' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|string',
        ]);

        $pembayaran_siswa = PembayaranSiswa::findOrFail($id);
        $pembayaran_siswa->update($validatedData);

        return response()->json(['message' => 'Pembayaran berhasil diperbarui', 'data' => $pembayaran_siswa]);
    }

    public function destroy($id)
    {
        $pembayaran_siswa = PembayaranSiswa::findOrFail($id);
        $pembayaran_siswa->delete();

        return response()->json(['message' => 'Pembayaran berhasil dihapus']);
    }
}
