<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseFormatSame;

class PengeluaranController extends Controller
{
    public function addPengeluaranKategori(Request $request)
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

    public function updatePengeluaranKategori(Request $request, string $id)
    {
        //define validation rules
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

    public function deletePengeluaranKategori(string $id)
    {

        $pengeluaranKategori = PengeluaranKategori::find($id);

        if (!$pengeluaranKategori) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'pengeluaran kategori tidak ditemukan'
                ],
                404
            );
        }

        $pengeluaranKategori->delete();

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran kategori berhasil dihapus',
            'data' => $pengeluaranKategori
        ]);
    }
}
