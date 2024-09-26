<?php

namespace App\Http\Controllers;

use App\Models\PendaftarDokumen;
use Illuminate\Http\Request;

class PendaftarDokumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dokumen = PendaftarDokumen::all();
        return response()->json($dokumen);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ppdb_id' => 'required|exists:ppdb,id',
            'akte_kelahiran' => 'required|string|max:255',
            'kartu_keluarga' => 'required|string|max:255',
            'ijazah' => 'required|string|max:255',
            'raport' => 'required|string|max:255',
        ]);

        $dokumen = PendaftarDokumen::create($validatedData);

        return response()->json([
            'message' => 'Dokumen berhasil ditambahkan',
            'data' => $dokumen,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $dokumen = PendaftarDokumen::findOrFail($id);
        return response()->json($dokumen);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'ppdb_id' => 'sometimes|required|exists:ppdb,id',
            'akte_kelahiran' => 'sometimes|required|string|max:255',
            'kartu_keluarga' => 'sometimes|required|string|max:255',
            'ijazah' => 'sometimes|required|string|max:255',
            'raport' => 'sometimes|required|string|max:255',
        ]);

        $dokumen = PendaftarDokumen::findOrFail($id);
        $dokumen->update($validatedData);

        return response()->json([
            'message' => 'Dokumen berhasil diperbarui',
            'data' => $dokumen,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $dokumen = PendaftarDokumen::findOrFail($id);
        $dokumen->delete();

        return response()->json([
            'message' => 'Dokumen berhasil dihapus'
        ], 200);
    }
}
