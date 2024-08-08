<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'nama_depan' => 'required|string',
            'nama_belakang' => 'required|string',
            'alamat' => 'required|string',
            'tempat_lahir' => 'required|string',
            'telepon' => 'required|string',
            'kelas_id' => 'required|integer',
            'orangtua_id' => 'required|integer',
            'village_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        $siswa = Siswa::create([
            'user_id' => $request->user_id,
            'nama_depan' => $request->nama_depan,
            'nama_belakang' => $request->nama_belakang,
            'alamat' => $request->alamat,
            'tempat_lahir' => $request->tempat_lahir,
            'telepon' => $request->telepon,
            'kelas_id' => $request->kelas_id,
            'orangtua_id' => $request->orangtua_id,
            'village_id' => $request->village_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil ditambahkan',
            'data' => $siswa
        ], 201);
    }
}
