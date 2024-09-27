<?php

namespace App\Http\Controllers;

use App\Models\PendaftarAkademik;
use Illuminate\Http\Request;

class PendaftarAkademikController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $akademik = PendaftarAkademik::all();
        return response()->json($akademik);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ppdb_id' => 'required|exists:ppdb,id',  // Validasi untuk memastikan ppdb_id ada di tabel ppdb
            'sekolah_asal' => 'required|string|max:255',
            'tahun_lulus' => 'required|date',  // Menggunakan validasi date
            'jurusan_tujuan' => 'required|string|max:255',
        ]);

        // Format tahun_lulus menjadi datetime sebelum disimpan
        $validatedData['tahun_lulus'] = date('Y-m-d H:i:s', strtotime($validatedData['tahun_lulus']));

        $akademik = PendaftarAkademik::create($validatedData);

        return response()->json([
            'message' => 'Data akademik berhasil ditambahkan',
            'data' => $akademik,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $akademik = PendaftarAkademik::findOrFail($id);
        return response()->json($akademik);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'ppdb_id' => 'sometimes|required|exists:ppdb,id',
            'sekolah_asal' => 'sometimes|required|string|max:255',
            'tahun_lulus' => 'sometimes|required|date',  // Menggunakan validasi date
            'jurusan_tujuan' => 'sometimes|required|string|max:255',
        ]);

        if (isset($validatedData['tahun_lulus'])) {
            // Format tahun_lulus menjadi datetime sebelum disimpan
            $validatedData['tahun_lulus'] = date('Y-m-d H:i:s', strtotime($validatedData['tahun_lulus']));
        }

        $akademik = PendaftarAkademik::findOrFail($id);
        $akademik->update($validatedData);

        return response()->json([
            'message' => 'Data akademik berhasil diperbarui',
            'data' => $akademik,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $akademik = PendaftarAkademik::findOrFail($id);
        $akademik->delete();

        return response()->json([
            'message' => 'Data akademik berhasil dihapus'
        ], 200);
    }
}
