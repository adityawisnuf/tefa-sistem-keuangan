<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswaCicilan;
use Illuminate\Http\Request;

class PembayaranSiswaCicilanController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'pembayaran_siswa_id' => 'required|exists:pembayaran_siswa,id',
            'nominal_cicilan' => 'required|numeric',
            'merchant_order_id' => 'required|string',  // Validasi untuk merchant_order_id
        ]);

        // Menyimpan data ke database
        $cicilan = PembayaranSiswaCicilan::create($validatedData);

        // Mengembalikan response sukses
        return response()->json([
            'message' => 'Cicilan siswa berhasil disimpan',
            'data' => $cicilan,
        ], 201);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengambil semua data cicilan siswa
        $cicilan = PembayaranSiswaCicilan::all();

        // Mengembalikan data cicilan siswa
        return response()->json($cicilan);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Mengambil data cicilan berdasarkan id
        $cicilan = PembayaranSiswaCicilan::findOrFail($id);

        // Mengembalikan data cicilan yang diminta
        return response()->json($cicilan);
    }

    public function update(Request $request, $id)
{
    // Validasi input
    $validatedData = $request->validate([
        'pembayaran_siswa_id' => 'sometimes|required|exists:pembayaran_siswa,id',
        'nominal_cicilan' => 'sometimes|required|numeric',
        'merchant_order_id' => 'sometimes|required|string', // Validasi untuk merchant_order_id
    ]);

    // Mencari cicilan berdasarkan id
    $cicilan = PembayaranSiswaCicilan::findOrFail($id);

    // Memperbarui data cicilan
    $cicilan->update($validatedData);

    // Mengembalikan response sukses
    return response()->json([
        'message' => 'Cicilan siswa berhasil diperbarui',
        'data' => $cicilan,
    ]);
}

/**
 * Remove the specified resource from storage.
 */
public function destroy($id)
{
    // Mencari cicilan berdasarkan id
    $cicilan = PembayaranSiswaCicilan::findOrFail($id);

    // Menghapus cicilan dari database
    $cicilan->delete();

    // Mengembalikan response sukses
    return response()->json([
        'message' => 'Cicilan siswa berhasil dihapus',
    ]);
}
}
