<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseFormatSame;

class PengeluaranController extends Controller
{
    public function addPengeluaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pengeluaran_kategori_id' => 'required|exists:pengeluaran_kategori,id',
            'keperluan' => 'required',
            'nominal' => 'required|integer',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = auth()->user()->role;

        if ($role == 'KepalaSekolah') {
            $pengeluaran = Pengeluaran::create([
                'pengeluaran_kategori_id' => $request->pengeluaran_kategori_id,
                'keperluan' => $request->keperluan,
                'nominal' => $request->nominal,
                'diajukan_pada' => now(),
            ]);
        } else if ($role == 'Bendahara') {
            $pengeluaran = Pengeluaran::create([
                'pengeluaran_kategori_id' => $request->pengeluaran_kategori_id,
                'keperluan' => $request->keperluan,
                'nominal' => $request->nominal,
                'diajukan_pada' => now(),
                'disetujui_pada' => now(),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran berhasil ditambahkan',
            'data' => $pengeluaran
        ]);
    }

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
