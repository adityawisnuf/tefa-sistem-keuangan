<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    // Menampilkan semua data pembayaran
    public function index()
    {
        $pembayarans = Pembayaran::all();
        return response()->json($pembayarans);
    }

    // Menampilkan form untuk membuat pembayaran baru (jika diperlukan)
    public function create()
    {
        //
    }

    // Menyimpan pembayaran baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'pembayaran_kategori_id' => 'required|exists:pembayaran_kategori,id',
            'nominal' => 'required|numeric',
            'status' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $pembayaran = Pembayaran::create($validatedData);

        return response()->json([
            'message' => 'Pembayaran berhasil ditambahkan',
            'data' => $pembayaran
        ], 201);
    }

    // Menampilkan detail pembayaran berdasarkan ID
    public function show($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        return response()->json($pembayaran);
    }

    // Menampilkan form untuk mengedit pembayaran (jika diperlukan)
    public function edit($id)
    {
        //
    }

    // Memperbarui data pembayaran
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'pembayaran_kategori_id' => 'required|exists:pembayaran_kategori,id',
            'nominal' => 'required|numeric',
            'status' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->update($validatedData);

        return response()->json([
            'message' => 'Pembayaran berhasil diperbarui',
            'data' => $pembayaran
        ], 200);
    }

    // Menghapus pembayaran (soft delete)
    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->delete();

        return response()->json([
            'message' => 'Pembayaran berhasil dihapus'
        ], 200);
    }
}
