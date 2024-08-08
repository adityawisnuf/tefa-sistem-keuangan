<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    public function getAllSiswa()
    {
        $siswa = Siswa::all();

        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil ditampilkan',
            'data' => $siswa
        ]);
    }

    public function show($id)
    {
        $siswa = Siswa::find($id);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil ditampilkan',
            'data' => $siswa
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'nama_depan' => 'required',
            'nama_belakang' => 'required',
            'alamat' => 'required',
            'village_id' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required', // Pastikan format tanggal sesuai
            'telepon' => 'required',
            'kelas_id' => 'required',
            'orangtua_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $siswa = Siswa::create([
            'user_id' => $request->user_id,
            'nama_depan' => $request->nama_depan,
            'nama_belakang' => $request->nama_belakang,
            'alamat' => $request->alamat,
            'village_id' => $request->village_id,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'telepon' => $request->telepon,
            'kelas_id' => $request->kelas_id,
            'orangtua_id' => $request->orangtua_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil ditambahkan',
            'data' => $siswa
        ], 201); // Menggunakan kode status 201 untuk berhasil menambahkan data
    }
}
