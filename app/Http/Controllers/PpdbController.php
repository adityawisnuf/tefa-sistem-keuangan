<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpdbRequest;
use App\Models\PembayaranDuitku;
use App\Models\Pendaftar;
use App\Models\Ppdb;
use App\Models\PendaftarDokumen;
use App\Models\PendaftarAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PpdbController extends Controller
{
    public function store(PpdbRequest $request)
    {
        DB::beginTransaction();
    
        try {
            // Generate a unique merchantOrderId
            $merchantOrderId = Str::uuid()->toString(); // Generate a UUID
    
            // Store files and get their paths
            $akteKelahiranPath = $request->file('akte_kelahiran')->store('documents');
            $kartuKeluargaPath = $request->file('kartu_keluarga')->store('documents');
            $ijazahPath = $request->file('ijazah')->store('documents');
            $raportPath = $request->file('raport')->store('documents');
    
            // Collect user data, including file paths
            $dataUserResponse = $request->only([
                'nama_depan',
                'nama_belakang',
                'jenis_kelamin',
                'nik',
                'email',
                'nisn',
                'tempat_lahir',
                'tgl_lahir',
                'alamat',
                'village_id',
                'nama_ayah',
                'nama_ibu',
                'tgl_lahir_ayah',
                'tgl_lahir_ibu',
                'sekolah_asal',
                'tahun_lulus',
                'jurusan_tujuan'
            ]);
    
            // Add file paths to the user data
            $dataUserResponse['akte_kelahiran'] = $akteKelahiranPath;
            $dataUserResponse['kartu_keluarga'] = $kartuKeluargaPath;
            $dataUserResponse['ijazah'] = $ijazahPath;
            $dataUserResponse['raport'] = $raportPath;
    
            // Create `PembayaranDuitku` record with encoded user data
            $pembayaranDuitku = PembayaranDuitku::create([
                'merchant_order_id' => $merchantOrderId,
                'status' => 'pending',
                'data_user_response' => json_encode($dataUserResponse),
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Data berhasil disimpan di PembayaranDuitku!',
                'pendaftar' => $pembayaranDuitku->toArray(),  // Use toArray() to check serialized data
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