<?php

namespace App\Http\Controllers;

use App\Models\PendaftarAkademik;
use App\Models\Ppdb;
use Illuminate\Http\Request;

class PendaftaranAkademikController extends Controller
{
    /**
     * Menampilkan daftar semua entri pendaftar akademik.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pendaftarAkademiks = PendaftarAkademik::all();
        return response()->json($pendaftarAkademiks);
    }

    /**
     * Menampilkan entri spesifik pendaftar akademik.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pendaftarAkademik = PendaftarAkademik::find($id);
        if (is_null($pendaftarAkademik)) {
            return response()->json(['message' => 'Pendaftar Akademik tidak ditemukan'], 404);
        }
        return response()->json($pendaftarAkademik);
    }

    /**
     * Menyimpan entri baru ke dalam database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'ppdb_id' => 'required|integer|exists:ppdb,id',
            'sekolah_asal' => 'required|string|max:255',
            'tahun_lulus' => 'required|date',
            'jurusan_tujuan' => 'required|string|max:255',
        ]);
    
        
    
        $pendaftarAkademik = PendaftarAkademik::create([
            'sekolah_asal' => $request->sekolah_asal,
            'tahun_lulus' => $request->tahun_lulus,
            'jurusan_tujuan' => $request->jurusan_tujuan,
        ]);
    
        // Mengembalikan response JSON
        return response()->json([
            'message' => 'Pendaftar Akademik berhasil disimpan',
            'data' => $pendaftarAkademik
        ], 201);
    }
    
    /**
     * Memperbarui entri spesifik di dalam database.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'ppdb_id' => 'integer|exists:ppdb,id',
            'sekolah_asal' => 'string|max:255',
            'tahun_lulus' => 'integer',
            'jurusan_tujuan' => 'string|max:255',
        ]);

        // Temukan entri pendaftar akademik
        $pendaftarAkademik = PendaftarAkademik::find($id);
        if (is_null($pendaftarAkademik)) {
            return response()->json(['message' => 'Pendaftar Akademik tidak ditemukan'], 404);
        }

        // Perbarui data
        $pendaftarAkademik->update($request->all());

        return response()->json([
            'message' => 'Pendaftar Akademik berhasil diperbarui',
            'data' => $pendaftarAkademik
        ]);
    }

    /**
     * Menghapus entri spesifik dari database.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pendaftarAkademik = PendaftarAkademik::find($id);
        if (is_null($pendaftarAkademik)) {
            return response()->json(['message' => 'Pendaftar Akademik tidak ditemukan'], 404);
        }

        $pendaftarAkademik->delete();

        return response()->json(['message' => 'Pendaftar Akademik berhasil dihapus']);
    }
}
