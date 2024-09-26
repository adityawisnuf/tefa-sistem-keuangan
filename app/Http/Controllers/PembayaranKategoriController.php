<?php

namespace App\Http\Controllers;

use App\Models\PembayaranKategori;
use Illuminate\Http\Request;

class PembayaranKategoriController extends Controller
{
    public function index()
    {
        $kategori = PembayaranKategori::all();
        return response()->json($kategori);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_pembayaran' => 'required|string',
            'tanggal_pembayaran' => 'required|date',
            'status' => 'required|string',
        ]);

        $kategori = PembayaranKategori::create($validatedData);

        return response()->json(['message' => 'Kategori pembayaran berhasil ditambahkan', 'data' => $kategori]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string',
        ]);

        $kategori = PembayaranKategori::findOrFail($id);
        $kategori->update($validatedData);

        return response()->json(['message' => 'Kategori berhasil diperbarui', 'data' => $kategori]);
    }

    public function destroy($id)        
    {
        $kategori = PembayaranKategori::findOrFail($id);
        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}
