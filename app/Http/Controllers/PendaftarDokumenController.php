<?php

namespace App\Http\Controllers;

use App\Models\PendaftarDokumen;
use App\Models\Ppdb;
use setasign\Fpdi\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use FPDF;
use Illuminate\Support\Facades\Storage;


class PendaftarDokumenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */




    public function index()
    {
        $pendaftarDokumens = PendaftarDokumen::all();
        return response()->json($pendaftarDokumens);
    }

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
            'akte_kelahiran' => 'required|file|mimes:pdf,max:2048',
            'kartu_keluarga' => 'required|file|mimes:pdf,max:2048',
            'ijazah' => 'required|file|mimes:pdf,max:2048',
            'raport' => 'required|file|mimes:pdf,max:2048',
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

        // Logging untuk debug
        Log::info('PendaftarDokumen created:', ['data' => $pendaftarDokumen]);

        // Mengembalikan respons JSON dengan pesan sukses
        return response()->json([
            'message' => 'Dokumen pendaftar berhasil disimpan!',
            'pendaftar_dokumen' => $pendaftarDokumen
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pendaftarDokumen = PendaftarDokumen::find($id);
        if (is_null($pendaftarDokumen)) {
            return response()->json(['message' => 'Pendaftar Dokumen not found'], 404);
        }
        return response()->json($pendaftarDokumen);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'ppdb_id' => 'integer|exists:ppdb,id',
            'akte_kelahiran' => 'file|mimes:pdf|max:2048',
            'kartu_keluarga' => 'file|mimes:pdf|max:2048',
            'ijazah' => 'file|mimes:pdf|max:2048',
            'raport' => 'file|mimes:pdf|max:2048',
        ]);

        $pendaftarDokumen = PendaftarDokumen::find($id);
        if (is_null($pendaftarDokumen)) {
            return response()->json(['message' => 'Pendaftar Dokumen not found'], 404);
        }

        if ($request->hasFile('akte_kelahiran')) {
            $akte_kelahiran = $request->file('akte_kelahiran')->store('documents');
            $pendaftarDokumen->akte_kelahiran = $akte_kelahiran;
        }

        if ($request->hasFile('kartu_keluarga')) {
            $kartu_keluarga = $request->file('kartu_keluarga')->store('documents');
            $pendaftarDokumen->kartu_keluarga = $kartu_keluarga;
        }

        if ($request->hasFile('ijazah')) {
            $ijazah = $request->file('ijazah')->store('documents');
            $pendaftarDokumen->ijazah = $ijazah;
        }

        if ($request->hasFile('raport')) {
            $raport = $request->file('raport')->store('documents');
            $pendaftarDokumen->raport = $raport;
        }

        $pendaftarDokumen->update($request->only([
            'ppdb_id',
        ]));

        return response()->json([
            'message' => 'Pendaftar Dokumen updated successfully',
            'pendaftar_dokumen' => $pendaftarDokumen
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pendaftarDokumen = PendaftarDokumen::find($id);
        if (is_null($pendaftarDokumen)) {
            return response()->json(['message' => 'Pendaftar Dokumen not found'], 404);
        }

        $pendaftarDokumen->delete();

        return response()->json(['message' => 'Pendaftar Dokumen deleted successfully']);
    }
}
