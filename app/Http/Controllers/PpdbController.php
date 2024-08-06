<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpdbRequest;
use App\Models\Pendaftar;
use App\Models\Ppdb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PpdbController extends Controller
{
 /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created pendaftar in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function store(PpdbRequest $request)
     {
         DB::beginTransaction();
 
         try {
             $ppdb = Ppdb::create(['status' => 1]);
 
             $pendaftar = $ppdb->pendaftar()->create($request->validated());
 
             // Simpan data dokumen
             $pendaftar->pendaftar_dokumen()->create([
                 'akte_kelahiran' => $request->file('akte_kelahiran')->store('documents'),
                 'kartu_keluarga' => $request->file('kartu_keluarga')->store('documents'),
                 'ijazah' => $request->file('ijazah')->store('documents'),
                 'raport' => $request->file('raport')->store('documents'),
             ]);
 
             // Simpan data akademik
             $pendaftar->pendaftar_akademik()->create($request->only('sekolah_asal', 'tahun_lulus', 'jurusan_tujuan'));
 
             DB::commit();
 
             return response()->json([
                 'message' => 'Pendaftaran berhasil!',
                 'pendaftar' => $pendaftar,
             ], 201);
         } catch (\Exception $e) {
             DB::rollback();
 
             Log::error('Pendaftaran gagal:', ['exception' => $e]);
 
             return response()->json([
                 'message' => 'Terjadi kesalahan saat pendaftaran.',
             ], 500);
         }
     }
 }
