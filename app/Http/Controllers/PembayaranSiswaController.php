<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;

class PembayaranSiswaController extends Controller
{
    // Menampilkan semua data pembayaran siswa
    public function index()
    {
        $pembayaran_siswa = PembayaranSiswa::with('siswa.user', 'pembayaran.pembayaran_kategori')->get();
        return response()->json($pembayaran_siswa);
    }

    // Menyimpan pembayaran siswa baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'pembayaran_id' => 'required|exists:pembayaran,id',
            'nominal' => 'required|numeric',
            'merchant_order_id' => 'required|string',
            'status' => 'required|boolean', // Menggunakan boolean untuk status
        ]);

        // Membuat entri baru di tabel pembayaran_siswa
        $pembayaran_siswa = PembayaranSiswa::create($validatedData);

        return response()->json([
            'message' => 'Pembayaran berhasil ditambahkan',
            'data' => $pembayaran_siswa
        ]);
    }

    // Menampilkan detail pembayaran siswa berdasarkan ID
    public function show($id)
    {
        $pembayaran_siswa = PembayaranSiswa::with('siswa.user', 'pembayaran.pembayaran_kategori')->findOrFail($id);
        return response()->json($pembayaran_siswa);
    }

    // Memperbarui data pembayaran siswa
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nominal' => 'sometimes|required|numeric',
            'merchant_order_id' => 'sometimes|required|string',
            'status' => 'sometimes|required|boolean', // Menggunakan boolean untuk status
        ]);

        $pembayaran_siswa = PembayaranSiswa::findOrFail($id);
        $pembayaran_siswa->update($validatedData);

        return response()->json([
            'message' => 'Pembayaran berhasil diperbarui',
            'data' => $pembayaran_siswa
        ]);
    }

    // Menghapus pembayaran siswa
    public function destroy($id)
    {
        $pembayaran_siswa = PembayaranSiswa::findOrFail($id);
        $pembayaran_siswa->delete();

        return response()->json(['message' => 'Pembayaran berhasil dihapus']);
    }
}
