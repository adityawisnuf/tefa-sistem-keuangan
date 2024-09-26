<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengeluaranKategoriController extends Controller
{
    public function index()
    {
        $pengeluaranKategori = PengeluaranKategori::all('id', 'nama', 'status');

        return response()->json([
            'success' => true,
            'message' => 'data pengeluaran kategori berhasil diambil',
            'data' => $pengeluaranKategori
        ]);
    }

    public function show(string $id)
    {
        $pengeluaranKategori = PengeluaranKategori::find($id, ['id', 'nama', 'status']);
        if (!$pengeluaranKategori) {
            return response()->json([
                'success' => false,
                'message' => 'data pengeluaran kategori tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'data pengeluaran kategori berhasil diambil',
            'data' => $pengeluaranKategori
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $pengeluaranKategori = PengeluaranKategori::create([
            'nama' => $request->nama
        ]);

        if (!$pengeluaranKategori) {
            return response()->json([
                'success' => false,
                'message' => 'gagal menambahkan pengeluaran kategori'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran kategori berhasil ditambahkan',
            'data' => $pengeluaranKategori
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nama'     => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $pengeluaranKategori = PengeluaranKategori::find($id);

        if (!$pengeluaranKategori) {
            return response()->json([
                'success' => false,
                'message' => 'pengeluaran kategori tidak ditemukan'
            ]);
        }

        $pengeluaranKategori->update([
            'nama' => $request->nama
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran kategori berhasil di ubah',
            'data' => $pengeluaranKategori
        ]);
    }

    public function destroy(string $id)
    {

        $pengeluaranKategori = PengeluaranKategori::find($id);

        if (!$pengeluaranKategori) {
            return response()->json([
                'success' => false,
                'message' => 'pengeluaran kategori tidak ditemukan'
            ], 404);
        }

        $pengeluaranKategori->delete();

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran kategori berhasil dihapus',
            'data' => $pengeluaranKategori
        ]);
    }
}
