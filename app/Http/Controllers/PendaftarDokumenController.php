<?php

namespace App\Http\Controllers;

use App\Models\PendaftarDokumen;
use Illuminate\Http\Request;

class PendaftarDokumenController extends Controller
{
      /**
     * Store a newly created pendaftar dokumen in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'ppdb_id' => 'required|integer|exists:ppdb,id',
            'akte_kelahiran' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'kartu_keluarga' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'ijazah' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'raport' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        // Upload file dan simpan pathnya
        $akte_kelahiran = $request->file('akte_kelahiran')->store('documents');
        $kartu_keluarga = $request->file('kartu_keluarga')->store('documents');
        $ijazah = $request->file('ijazah')->store('documents');
        $raport = $request->file('raport')->store('documents');

        // Membuat data pendaftar dokumen baru
        $pendaftarDokumen = PendaftarDokumen::create([
            'ppdb_id' => $request->ppdb_id,
            'akte_kelahiran' => $akte_kelahiran,
            'kartu_keluarga' => $kartu_keluarga,
            'ijazah' => $ijazah,
            'raport' => $raport,
        ]);

        // Mengembalikan respons JSON dengan pesan sukses
        return response()->json([
            'message' => 'Dokumen pendaftar berhasil disimpan!',
            'pendaftar_dokumen' => $pendaftarDokumen
        ], 201);
    }
}
