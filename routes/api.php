<?php

use App\Http\Controllers\BukuKasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Models\PembayaranSiswa; // Pastikan ini adalah model yang tepat
use Barryvdh\DomPDF\Facade\Pdf;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);
    Route::get('/kas', [BukuKasController::class, 'index']);

    // Laporan Pembayaran
    Route::get('/laporan/pembayaran', function () {
        $tgl_awal = request('tgl_awal');
        $tgl_akhir = request('tgl_akhir');

        if ($tgl_awal && $tgl_akhir) {
            $pembayaran = PembayaranSiswa::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
            $fileName = "Pembayaran {$tgl_awal} - {$tgl_akhir}.pdf";
        } else {
            $pembayaran = PembayaranSiswa::all();
            $fileName = "Data Keseluruhan Pembayaran.pdf";
        }

        $data = ['pembayarans' => $pembayaran];
        $pdf = Pdf::loadView('print.pembayaran', $data);

        return $pdf->stream($fileName);
    })->name('laporan.pembayaran');
});
