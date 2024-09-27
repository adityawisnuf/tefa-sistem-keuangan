<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Orangtua;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with(['user', 'orangtua', 'village', 'kelas'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil ditampilkan',
            'data' => $siswa
        ]);
    }

    public function show($id)
    {
        $siswa = Siswa::with(['user', 'orangtua', 'village', 'kelas'])->find($id);

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
            'tanggal_lahir' => 'required',
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
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'nama_depan' => 'required',
            'nama_belakang' => 'required',
            'alamat' => 'required',
            'village_id' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
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

        $updatesiswa = Siswa::find($id);

        if (!$updatesiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data Siswa tidak ditemukan'
            ], 404);
        }

        if ($request->user_id) {
            $updatesiswa->update([
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
        } else {
            $updatesiswa->update([

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
        }



        return response()->json([
            'success' => true,
            'message' => 'Data Siswa berhasil diperbarui',
            'data' => $updatesiswa
        ]);
    }

    public function destroy($id)
    {
        $deletesiswa = Siswa::find($id);

        if (!$deletesiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data Siswa tidak ditemukan'
            ], 404);
        }

        $deletesiswa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Siswa berhasil dihapus'
        ]);
    }

    public function filterByOrangTua($id)
{
    // Cari data orang tua berdasarkan ID
    $orangTua = Orangtua::find($id);

    if (!$orangTua) {
        return response()->json([
            'success' => false,
            'message' => 'Orang tua tidak ditemukan',
        ], 404);
    }

    // Ambil semua siswa yang memiliki ID orang tua yang sama
    $siswa = Siswa::where('orangtua_id', $id)->get();

    return response()->json([
        'success' => true,
        'message' => 'Data siswa dengan orang tua berhasil ditemukan',
        'data' => $siswa,
    ], 200);
}
}
