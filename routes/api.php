<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PengeluaranKategoriController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::middleware('checkrole:Bendahara,KepalaSekolah')->group(function () {
        // pengeluaran
        Route::post('pengeluaran', [PengeluaranController::class, 'addPengeluaran']);
        Route::delete('pengeluaran/{id}', [PengeluaranController::class, 'deletePengeluaran']);
        Route::patch('pengeluaran/{id}', [PengeluaranController::class, 'updatePengeluaran']);
    });
    
    Route::patch('/pengeluaran/{id}/accept', [PengeluaranController::class, 'acceptPengeluaran'])->middleware('checkrole:Bendahara');

    // pengeluaran kategori
    Route::post('pengeluaran/kategori', [PengeluaranKategoriController::class, 'addPengeluaranKategori']);
    Route::delete('pengeluaran/kategori/{id}', [PengeluaranKategoriController::class, 'deletePengeluaranKategori']);
    Route::patch('pengeluaran/kategori/{id}', [PengeluaranKategoriController::class, 'updatePengeluaranKategori']);
});
