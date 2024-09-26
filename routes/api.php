<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PembayaranSiswaController;
use App\Http\Controllers\PembayaranDuitkuController;
use App\Http\Controllers\PembayaranKategoriController;
use App\Http\Controllers\PembayaranSiswaCicilanController;
use App\Http\Controllers\PengeluaranAnalysis;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PengeluaranKategoriController;
use App\Http\Controllers\PpdbController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout', [LogoutController::class, 'logout']);
});

// data master
Route::apiResource('orangtua', OrangTuaController::class);
Route::apiResource('sekolah', SekolahController::class);
Route::apiResource('kelas', KelasController::class);
Route::apiResource('siswa', SiswaController::class);

// sortir kelas
Route::get('filter-kelas', [KelasController::class, 'filterKelas']);
Route::get('/filter-sekolah', [KelasController::class, 'filterBySekolah']);
Route::get('filter-orangtua/{id}', [SiswaController::class, 'filterByOrangTua']);

// pembayaran
Route::apiResource('pembayaran_siswa', PembayaranSiswaController::class);
Route::apiResource('pembayaran_duitku', PembayaranDuitkuController::class);
Route::apiResource('pembayaran', PembayaranController::class);
Route::apiResource('pembayaransiswacicilan', PembayaranSiswaCicilanController::class);
Route::apiResource('pembayaran_kategori', PembayaranKategoriController::class);
Route::apiResource('pembayaran-siswa', PembayaranSiswaController::class);

// pengumuman
Route::middleware(['auth:api'])->group(function () {
    // Pengeluaran Kategori
    Route::apiResource('pengeluaran/kategori', PengeluaranKategoriController::class);

    // Pengeluaran actions
    Route::get('pengeluaran/disetujui', [PengeluaranController::class, 'getPengeluaranDisetujui']);
    Route::get('pengeluaran/belum-disetujui', [PengeluaranController::class, 'getPengeluaranBelumDisetujui']);
    Route::get('pengeluaran/riwayat', [PengeluaranController::class, 'riwayatPengeluaran']);
    Route::get('pengeluaran/periode/{periode}', [PengeluaranController::class, 'rekapitulasiPengeluaran']);
    Route::get('pengeluaran/analisis/{periode}', [PengeluaranAnalysis::class, 'getPengeluaranPeriode']);

    // Pengeluaran resource
    Route::apiResource('pengeluaran', PengeluaranController::class);
    Route::patch('/pengeluaran/{id}/accept', [PengeluaranController::class, 'acceptPengeluaran']);
    Route::patch('/pengeluaran/{id}/reject', [PengeluaranController::class, 'rejectPengeluaran']);


    Route::group(['middleware' => 'checkrole:Kepala Sekolah'], function () {
        Route::put('/pengumuman/{id}/approve', [PengumumanController::class, 'approve']);
        Route::put('/pengumuman/{id}/reject', [PengumumanController::class, 'reject']);
    });

    // pengumuman
    Route::group(['middleware' => 'checkrole:Admin,Bendahara'], function () {
        Route::get('/pengumuman/approved', [PengumumanController::class, 'approvedAnnouncements']);
        Route::get('/pengumuman/rejected', [PengumumanController::class, 'rejectedAnnouncements']);
    });

    Route::group(['middleware' => 'checkrole:Kepala Sekolah,Admin,Bendahara'], function () {
        Route::get('/pengumuman/submitted', [PengumumanController::class, 'submittedAnnouncements']);
        Route::post('/pengumuman', [PengumumanController::class, 'store']);
        Route::put('/pengumuman/{id}', [PengumumanController::class, 'update']);
        Route::delete('/pengumuman/{id}', [PengumumanController::class, 'destroy']);
    });

    Route::group(['middleware' => 'checkrole:Kepala Sekolah,Orang Tua,Siswa,Admin,Bendahara'], function () {
        Route::get('/pengumuman/{id}', [PengumumanController::class, 'show']);
        Route::get('/pengumuman', [PengumumanController::class, 'AllAnnouncements']);
    });
});

// route ppdb
Route::get('/ppdb', [PpdbController::class, 'index']);            // GET: Menampilkan semua data PPDB
Route::post('/ppdb', [PpdbController::class, 'store']);           // POST: Menambahkan data baru
Route::get('/ppdb/{id}', [PpdbController::class, 'show']);        // GET: Menampilkan data berdasarkan ID
Route::put('/ppdb/{id}', [PpdbController::class, 'update']);      // PUT: Mengubah data berdasarkan ID
Route::delete('/ppdb/{id}', [PpdbController::class, 'destroy']);  // DELETE: Menghapus data berdasarkan ID
