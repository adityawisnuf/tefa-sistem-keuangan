<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class SekolahController extends Controller
{
    public function getAllSekolah()
    {
        $sekolah = Sekolah::all();

        return response()->json(
            [
                'success' => true,
                'message' => 'sekolah berhasil ditampilkan',
                'data' => $sekolah
            ]
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'telepon' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $sekolah = Sekolah::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon
        ]);

        return response()->json([
            'success' => true,
            'message' => 'sekolah berhasil ditambahkan',
            'data' => $sekolah
        ]);
    }
}
