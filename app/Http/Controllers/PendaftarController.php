<?php

namespace App\Http\Controllers;

use App\Models\Pendaftar;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendaftarCreated;
use App\Models\Ppdb;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PendaftarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pendaftars = Pendaftar::all();
        return response()->json($pendaftars);
    }

    /**
     * Store a newly created pendaftar in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function store(Request $request)
     {
         $request->validate([
             'nama_depan' => 'required|string|max:255',
             'nama_belakang' => 'required|string|max:255',
             'jenis_kelamin' => 'required|string|max:10',
             'nik' => 'required|integer|unique:pendaftar',
             'email' => 'required|string|email|max:255',
             'nisn' => 'required|integer|unique:pendaftar',
             'tempat_lahir' => 'required|string|max:255',
             'tgl_lahir' => 'required|date',
             'alamat' => 'required|string',
             'village_id' => 'required|integer|exists:villages,id',
             'nama_ayah' => 'required|string|max:255',
             'nama_ibu' => 'required|string|max:255',
             'tgl_lahir_ayah' => 'required|date',
             'tgl_lahir_ibu' => 'required|date',
         ]);

        $ppdb = Ppdb::create([
            'status' => 1,
        ]);

         $pendaftar = Pendaftar::create([
             'ppdb_id' => $ppdb->id,
             'nama_depan' => $request->input('nama_depan'),
             'nama_belakang' => $request->input('nama_belakang'),
             'jenis_kelamin' => $request->input('jenis_kelamin'),
             'nik' => $request->input('nik'),
             'email' => $request->input('email'),
             'nisn' => $request->input('nisn'),
             'tempat_lahir' => $request->input('tempat_lahir'),
             'tgl_lahir' => $request->input('tgl_lahir'),
             'alamat' => $request->input('alamat'),
             'village_id' => $request->input('village_id'),
             'nama_ayah' => $request->input('nama_ayah'),
             'nama_ibu' => $request->input('nama_ibu'),
             'tgl_lahir_ayah' => $request->input('tgl_lahir_ayah'),
             'tgl_lahir_ibu' => $request->input('tgl_lahir_ibu'),
         ]);

         Notification::send($pendaftar, new EmailVerificationNotification());

         return response()->json([
             'message' => 'Anda telah berhasil mendaftar!',
             'pendaftar' => $pendaftar
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
        $pendaftar = Pendaftar::find($id);
        if (is_null($pendaftar)) {
            return response()->json(['message' => 'Pendaftar not found'], 404);
        }
        return response()->json($pendaftar);
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
            'nama_depan' => 'string|max:255',
            'nama_belakang' => 'string|max:255',
            'jenis_kelamin' => 'string|max:10',
            'nik' => 'integer|unique:pendaftar,nik,' . $id,
            'email' => 'string|email|max:255|unique:pendaftar,email,' . $id,
            'nisn' => 'integer|unique:pendaftar,nisn,' . $id,
            'tempat_lahir' => 'string|max:255',
            'tgl_lahir' => 'date',
            'alamat' => 'string',
            'village_id' => 'integer|exists:villages,id',
            'nama_ayah' => 'string|max:255',
            'nama_ibu' => 'string|max:255',
            'tgl_lahir_ayah' => 'date',
            'tgl_lahir_ibu' => 'date',
        ]);

        $pendaftar = Pendaftar::find($id);
        if (is_null($pendaftar)) {
            return response()->json(['message' => 'Pendaftar not found'], 404);
        }

        $pendaftar->update($request->all());

        return response()->json([
            'message' => 'Pendaftar updated successfully',
            'pendaftar' => $pendaftar
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
        $pendaftar = Pendaftar::find($id);
        if (is_null($pendaftar)) {
            return response()->json(['message' => 'Pendaftar not found'], 404);
        }

        $pendaftar->delete();

        return response()->json(['message' => 'Pendaftar deleted successfully']);
    }
}
