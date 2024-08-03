<?php

namespace App\Http\Controllers;

use App\Models\PendaftarAkademik;
use Illuminate\Http\Request;

class PendaftaranAkademikController extends Controller
{
     /**
     * Menampilkan formulir untuk membuat entri baru.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Menampilkan formulir pembuatan baru
        return response()->json([
            'message' => 'Formulir pembuatan baru'
        ], 200);
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
            'tahun_lulus' => 'required|integer',
            'jurusan_tujuan' => 'required|string|max:255',
        ]);

        // Simpan data ke dalam database
        $pendaftarAkademik = PendaftarAkademik::create([
            'ppdb_id' => $request->input('ppdb_id'),
            'sekolah_asal' => $request->input('sekolah_asal'),
            'tahun_lulus' => $request->input('tahun_lulus'),
            'jurusan_tujuan' => $request->input('jurusan_tujuan'),
        ]);

        // Mengembalikan response JSON
        return response()->json([
            'message' => 'Pendaftar Akademik berhasil disimpan',
            'data' => $pendaftarAkademik
        ], 201);
    }
}
