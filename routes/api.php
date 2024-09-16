<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PengeluaranAnalysis;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PengeluaranKategoriController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::middleware('checkrole:Bendahara,KepalaSekolah,Admin')->group(function () {
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
    });

    Route::patch('/pengeluaran/{id}/accept', [PengeluaranController::class, 'acceptPengeluaran'])
        ->middleware('checkrole:Bendahara');
});
