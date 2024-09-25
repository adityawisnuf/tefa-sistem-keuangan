<?php

namespace App\Http\Controllers;

use App\Models\Orangtua;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrangTuaController extends Controller
{


    public function getAllSekolah()
    {
        try {
            $orangtuashow = Orangtua::all();

            return response()->json([
                'success' => true,
                'message' => 'sekolah berhasil ditampilkan',
                'data' => $orangtuashow
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data sekolah',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        // Find the sekolah by ID
        $orangtua = Orangtua::find($id);

        if (!$orangtua) {
            return response()->json([
                'success' => false,
                'message' => 'Data Orangtua tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Orangtua berhasil ditampilkan',
            'data' => $orangtua
        ], 200);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'nama' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $orangtua = Orangtua::create([

            'nama' => $request->nama,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'orangtua berhasil ditambahkan',
            'data' => $orangtua
        ]);

    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable',
            'nama' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $orangtua = Orangtua::find($id);

        if (!$orangtua) {
            return response()->json([
                'success' => false,
                'message' => 'orangtua tidak ditemukan'
            ], 404);
        }

        if ($request->user_id) {
            $orangtua->update([
                'user_id' => $request->user_id,
                'nama' => $request->nama,

            ]);
        } else {
            $orangtua->update([

                'nama' => $request->nama,

            ]);
        }



        return response()->json([
            'success' => true,
            'message' => 'orangtua berhasil diperbarui',
            'data' => $orangtua
        ]);
    }

    public function destroy($id)
    {
        $orangtua = Orangtua::find($id);

        if (!$orangtua) {
            return response()->json([
                'success' => false,
                'message' => 'orangtua tidak ditemukan'
            ], 404);
        }

        $orangtua->delete();

        return response()->json([
            'success' => true,
            'message' => 'orangtua berhasil dihapus'
        ]);
    }


}
