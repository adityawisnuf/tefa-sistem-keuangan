<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\PengumumanAdminController;
use App\Http\Controllers\PengumumanKepalaSekolahController;
use App\Http\Controllers\PengumumanOrangTuaController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);


    // pengeluaran
    Route::post('pengeluaran/kategori', [PengeluaranController::class, 'addPengeluaranKategori']);
});

Route::get('orangtua', [OrangTuaController::class, 'getAllSekolah']);
Route::get('orangtua/{id}', [OrangTuaController::class, 'show']);
Route::post('orangtua', [OrangTuaController::class, 'store']);
Route::patch('orangtua/{id}', [OrangTuaController::class, 'update']);
Route::delete('orangtua/{id}', [OrangTuaController::class, 'destroy']);


Route::post('sekolah', [SekolahController::class, 'store']);

// get sekolah
Route::get('sekolah', [SekolahController::class, 'getAllSekolah']);

// update sekolah
Route::put('/sekolah/{id}', [SekolahController::class, 'update']);

// delete sekolah
Route::delete('/sekolah/{id}', [SekolahController::class, 'destroy']);
Route::get('sekolah/{id}', [SekolahController::class, 'show']);


// kelas crud
Route::get('kelas', [KelasController::class, 'index']);
Route::get('kelas/{id}', [KelasController::class, 'show']);
Route::post('kelas', [KelasController::class, 'store']);
Route::put('kelas/{id}', [KelasController::class, 'update']);
Route::delete('kelas/{id}', [KelasController::class, 'destroy']);



// data siswa
Route::get('siswa', [SiswaController::class, 'getAllSiswa']);
Route::get('siswa/{id}', [SiswaController::class, 'show']);
Route::post('siswa', [SiswaController::class, 'store']);
Route::put('siswa/{id}', [SiswaController::class, 'updateSiswa']);
Route::delete('siswa/{id}', [SiswaController::class, 'destroy']);
// close data siswa


// pengumuman
Route::middleware(['checkrole:KepalaSekolah'])->group(function () {
    Route::get('/pengumuman', [PengumumanKepalaSekolahController::class, 'allApprovedAnnouncements']);
    Route::get('/pengumuman/submitted', [PengumumanKepalaSekolahController::class, 'allSubmittedAnnouncements']);
    Route::post('/pengumuman', [PengumumanKepalaSekolahController::class, 'store']);
    Route::get('/pengumuman/{id}', [PengumumanKepalaSekolahController::class, 'show']);
    Route::put('/pengumuman/{id}', [PengumumanKepalaSekolahController::class, 'update']);
    Route::delete('/pengumuman/{id}', [PengumumanKepalaSekolahController::class, 'destroy']);
    Route::put('/pengumuman/{id}/approve', [PengumumanKepalaSekolahController::class, 'approve']);
    Route::put('/pengumuman/{id}/reject', [PengumumanKepalaSekolahController::class, 'reject']);
});

Route::middleware(['checkrole:Admin,Bendahara'])->group(function () {
    Route::get('/pengumuman', [PengumumanAdminController::class, 'approvedAnnouncements']);
    Route::get('/pengumuman/submitted', [PengumumanAdminController::class, 'submittedAnnouncements']);
    Route::get('/pengumuman/rejected', [PengumumanAdminController::class, 'rejectedAnnouncements']);
    Route::post('/pengumuman', [PengumumanAdminController::class, 'store']);
    Route::get('/pengumuman/{id}', [PengumumanAdminController::class, 'show']);
    Route::put('/pengumuman/{id}', [PengumumanAdminController::class, 'update']);
    Route::delete('/pengumuman/{id}', [PengumumanAdminController::class, 'destroy']);
});

Route::middleware(['checkrole:OrangTua,Siswa'])->group(function () {
    Route::get('/pengumuman', [PengumumanOrangTuaController::class, 'allApprovedAnnouncements']);
    Route::get('/pengumuman/{id}', [PengumumanOrangTuaController::class, 'show']);
});
