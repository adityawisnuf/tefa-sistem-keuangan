<?php

use App\Http\Controllers\Api\AnggaranController;
use App\Models\Anggaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);
});
Route::middleware('auth:api')->apiResource('/anggaran', AnggaranController::class);

Route::get('/laporan/anggaran', function () {
    $tgl_awal = request('tgl_awal');
    $tgl_akhir = request('tgl_akhir');

    if ($tgl_awal && $tgl_akhir) {
        $anggaran = Anggaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
        $fileName = "Anggaran {$tgl_awal} - {$tgl_akhir}.pdf";
    } else {
        $anggaran = Anggaran::all();
        $fileName = "Data Keseluruhan Anggaran.pdf";
    }

    $data = ['anggarans' => $anggaran];
    $pdf = Pdf::loadView('print.anggaran', $data);

    return $pdf->stream($fileName);
})->name('laporan.anggaran');
