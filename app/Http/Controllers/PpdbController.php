<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpdbRequest;
use App\Models\Pendaftar;
use App\Models\Ppdb;
use App\Models\PendaftarDokumen;
use App\Models\PendaftarAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PpdbController extends Controller
{
    public function store(PpdbRequest $request)
    {
        DB::beginTransaction();

        try {

            // $ppdb = Ppdb::where('id')->first();

            $pendaftar = Pendaftar::create([
                'ppdb_id'   => 2,
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

            // Simpan data dokumen
            PendaftarDokumen::create([
                'ppdb_id'   => 2,

                'akte_kelahiran' => $request->file('akte_kelahiran')->store('documents'),
                'kartu_keluarga' => $request->file('kartu_keluarga')->store('documents'),
                'ijazah' => $request->file('ijazah')->store('documents'),
                'raport' => $request->file('raport')->store('documents'),
            ]);

            // Simpan data akademik
            PendaftarAkademik::create([
                'ppdb_id'   => 2,
                'sekolah_asal' => $request->sekolah_asal,
                'tahun_lulus' => $request->tahun_lulus,
                'jurusan_tujuan' => $request->jurusan_tujuan,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pendaftaran berhasil!',
                'pendaftar' => $pendaftar,
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Pendaftaran gagal:', [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat pendaftaran. Silakan coba lagi.',
            ], 500);
        }
    }
}
