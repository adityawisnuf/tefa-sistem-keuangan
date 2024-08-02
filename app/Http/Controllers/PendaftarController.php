<?php

namespace App\Http\Controllers;

use App\Models\Pendaftar;
use Illuminate\Http\Request;

class PendaftarController extends Controller
{
    /**
     * Store a newly created pendaftar in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'ppdb_id' => 'required|integer|exists:ppdb,id',
            'nama_depan' => 'required|string|max:255',
            'nama_belakang' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|max:10',
            'tempat_lahir' => 'required|string|max:255',
            'tgl_lahir' => 'required|date',
            'alamat' => 'required|string',
            
            'nama_ayah' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:255',
            'tgl_lahir_ayah' => 'required|date',
            'tgl_lahir_ibu' => 'required|date',
        ]);

        // Membuat data pendaftar baru
        $pendaftar = Pendaftar::create($request->all());

        // Mengembalikan respons JSON dengan pesan sukses
        return response()->json([
            'message' => 'Anda telah berhasil mendaftar!',
            'pendaftar' => $pendaftar
        ], 201);
    }
}
