<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SekolahController extends Controller
{
    // Get all sekolah
    public function getAllSekolah()
    {
        try {
            $sekolah = Sekolah::all();

            return response()->json([
                'success' => true,
                'message' => 'sekolah berhasil ditampilkan',
                'data' => $sekolah
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data sekolah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get single sekolah by ID
    public function show($id)
    {
        // Find the sekolah by ID
        $sekolah = Sekolah::find($id);

        if (!$sekolah) {
            return response()->json([
                'success' => false,
                'message' => 'sekolah tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'sekolah berhasil ditampilkan',
            'data' => $sekolah
        ], 200);
    }

    // Create data sekolah
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'telepon' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $sekolah = Sekolah::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon
        ]);

        return response()->json([
            'success' => true,
            'message' => 'sekolah berhasil ditambahkan',
            'data' => $sekolah
        ]);
    }

    // Update data sekolah
    public function update(Request $request, $id)
    {
        // Find the sekolah by ID
        $sekolah = Sekolah::find($id);

        if (!$sekolah) {
            return response()->json([
                'success' => false,
                'message' => 'sekolah tidak ditemukan'
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string',
            'alamat' => 'sometimes|required|string',
            'telepon' => 'sometimes|required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update the sekolah with new data
        $sekolah->nama = $request->get('nama', $sekolah->nama);
        $sekolah->alamat = $request->get('alamat', $sekolah->alamat);
        $sekolah->telepon = $request->get('telepon', $sekolah->telepon);
        $sekolah->save();

        return response()->json([
            'success' => true,
            'message' => 'sekolah berhasil diupdate',
            'data' => $sekolah
        ]);
    }

    // Delete data sekolah
    public function destroy($id)
    {
        // Find the sekolah by ID
        $sekolah = Sekolah::find($id);

        if (!$sekolah) {
            return response()->json([
                'success' => false,
                'message' => 'sekolah tidak ditemukan'
            ], 404);
        }

        // Delete the sekolah
        $sekolah->delete();

        return response()->json([
            'success' => true,
            'message' => 'sekolah berhasil dihapus'
        ]);
    }
}
