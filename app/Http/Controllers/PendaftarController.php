<?php

namespace App\Http\Controllers;

use App\Models\Pendaftar;
use Illuminate\Http\Request;

class PendaftarController extends Controller
{
    /**
     * Menampilkan semua pendaftar
     */
    public function index()
    {
        $pendaftar = Pendaftar::all();
        return response()->json($pendaftar);
    }

    /**
     * Menyimpan pendaftar baru
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ppdb_id' => 'required|exists:ppdb,id',  // Asumsi 'ppdb' adalah tabel yang berisi id PPDB
            'nama_depan' => 'required|string|max:255',
            'nama_belakang' => 'nullable|string|max:255',
            'jenis_kelamin' => 'required|string',
            'tempat_lahir' => 'required|string|max:255',
            'tgl_lahir' => 'required|date',
            'alamat' => 'required|string',
            'village_id' => 'required|integer',
            'nama_ayah' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:255',
            'tgl_lahir_ayah' => 'required|date',
            'tgl_lahir_ibu' => 'required|date',
        ]);

        $pendaftar = Pendaftar::create($validatedData);

        return response()->json([
            'message' => 'Pendaftar berhasil ditambahkan',
            'data' => $pendaftar,
        ], 201);
    }

    /**
     * Menampilkan pendaftar berdasarkan id
     */
    public function show($id)
    {
        $pendaftar = Pendaftar::findOrFail($id);
        return response()->json($pendaftar);
    }

    /**
     * Memperbarui pendaftar
     */
    public function update(Request $request, $id)
    {
        $pendaftar = Pendaftar::findOrFail($id);

        $validatedData = $request->validate([
            'ppdb_id' => 'sometimes|required|exists:ppdb,id',
            'nama_depan' => 'sometimes|required|string|max:255',
            'nama_belakang' => 'nullable|string|max:255',
            'jenis_kelamin' => 'sometimes|required|string',
            'tempat_lahir' => 'sometimes|required|string|max:255',
            'tgl_lahir' => 'sometimes|required|date',
            'alamat' => 'sometimes|required|string',
            'village_id' => 'sometimes|required|integer',
            'nama_ayah' => 'sometimes|required|string|max:255',
            'nama_ibu' => 'sometimes|required|string|max:255',
            'tgl_lahir_ayah' => 'sometimes|required|date',
            'tgl_lahir_ibu' => 'sometimes|required|date',
        ]);

        $pendaftar->update($validatedData);

        return response()->json([
            'message' => 'Pendaftar berhasil diperbarui',
            'data' => $pendaftar,
        ]);
    }

    /**
     * Menghapus pendaftar
     */
    public function destroy($id)
    {
        $pendaftar = Pendaftar::findOrFail($id);
        $pendaftar->delete();

        return response()->json([
            'message' => 'Pendaftar berhasil dihapus',
        ]);
    }
}
