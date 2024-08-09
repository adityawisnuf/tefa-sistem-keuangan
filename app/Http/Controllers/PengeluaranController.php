<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Illuminate\Http\RedirectResponse;
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

        if ($role !== 'Bendahara') {
            $pengeluaran = Pengeluaran::create([
                'pengeluaran_kategori_id' => $request->pengeluaran_kategori_id,
                'keperluan' => $request->keperluan,
                'nominal' => $request->nominal,
                'diajukan_pada' => now(),
            ]);
        }

        $pengeluaran = Pengeluaran::create([
            'pengeluaran_kategori_id' => $request->pengeluaran_kategori_id,
            'keperluan' => $request->keperluan,
            'nominal' => $request->nominal,
            'diajukan_pada' => now(),
            'disetujui_pada' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran berhasil ditambahkan',
            'data' => $pengeluaran
        ]);
    }

    public function deletePengeluaran(string $id)
    {

        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'pengeluaran tidak ditemukan'
                ],
                404
            );
        }

        $pengeluaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran berhasil dihapus',
            'data' => $pengeluaran
        ]);
    }

    public function acceptPengeluaran(string $id)
    {
        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'pengeluaran tidak ditemukan'
            ], 404);
        }

        $role = auth()->user()->role;
        if ($role !== 'Bendahara') {
            abort(403);
        }

        if ($pengeluaran->disetujui_pada) {
            return response()->json([
                'success' => false,
                'message' => 'pengeluaran sudah diterima',
            ], 409);
        }

        $pengeluaran->update([
            'disetujui_pada' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran berhasil diterima',
            'data' => $pengeluaran
        ]);
    }
}
