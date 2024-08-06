<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Ppdb;
use App\Models\PendaftarDokumen;
use App\Models\PendaftarAkademik;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class PendaftarKomplitController extends Controller
{
    public function store(Request $request)
    {
        // Validasi data pendaftar
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

        // Validasi dokumen pendaftar
        $request->validate([
            'ppdb_id' => 'required|integer|exists:ppdb,id',
            'akte_kelahiran' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'kartu_keluarga' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'ijazah' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'raport' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        // Upload file dan simpan pathnya
        $akte_kelahiran = $request->file('akte_kelahiran')->store('documents');
        $kartu_keluarga = $request->file('kartu_keluarga')->store('documents');
        $ijazah = $request->file('ijazah')->store('documents');
        $raport = $request->file('raport')->store('documents');

        // Membuat data pendaftar dokumen baru
        $pendaftarDokumen = PendaftarDokumen::create([
            'ppdb_id' => $request->ppdb_id,
            'akte_kelahiran' => $akte_kelahiran,
            'kartu_keluarga' => $kartu_keluarga,
            'ijazah' => $ijazah,
            'raport' => $raport,
        ]);

        // Logging untuk debug
        Log::info('PendaftarDokumen created:', ['data' => $pendaftarDokumen]);

        // Mengirim notifikasi verifikasi email
        Notification::send($pendaftar, new EmailVerificationNotification());

        // Validasi data akademik pendaftar
        $request->validate([
            'ppdb_id' => 'required|integer|exists:ppdb,id',
            'sekolah_asal' => 'required|string|max:255',
            'tahun_lulus' => 'required|date',
            'jurusan_tujuan' => 'required|string|max:255',
        ]);

        $pendaftarAkademik = PendaftarAkademik::create([
            'ppdb_id' => $ppdb->id,
            'sekolah_asal' => $request->sekolah_asal,
            'tahun_lulus' => $request->tahun_lulus,
            'jurusan_tujuan' => $request->jurusan_tujuan,
        ]);

        // Mengembalikan respons JSON dengan pesan sukses
        return response()->json([
            'message' => 'Pendaftar berhasil disimpan!',
            'pendaftar' => $pendaftar,
            'pendaftar_dokumen' => $pendaftarDokumen,
            'pendaftar_akademik' => $pendaftarAkademik,
        ], 201);
    }
}
