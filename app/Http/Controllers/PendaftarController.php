<?php

namespace App\Http\Controllers;

use App\Models\Pendaftar;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientCreated;
use App\Mail\PendaftarCreated;
use Illuminate\Support\Facades\Notification;

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

        $request->validate([
            'ppdb_id' => 'required|integer|exists:ppdb,id',
            'nama_depan' => 'required|string|max:255',
            'nama_belakang' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|max:10',
            'nik' => 'required|integer|unique:pendaftar',
            'email'=> 'required|string|email|max:255',
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

        // Membuat data pendaftar baru
        $pendaftar = Pendaftar::create($request->all());

        Notification::send($pendaftar, new EmailVerificationNotification());

        // Mengembalikan respons JSON dengan pesan sukses
        return response()->json([
            'message' => 'Anda telah berhasil mendaftar!',
            'pendaftar' => $pendaftar
        ], 201);
    }
}

