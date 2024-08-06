<?php

use App\Http\Controllers\Api\AsetSekolahController;
use App\Models\AsetSekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PrintInventaris;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);
    Route::get('inventaris', PrintInventaris::class);
});
Route::middleware('auth:api')->apiResource('/assets', AsetSekolahController::class);

Route::get('/laporan/inventaris', function () {
    $tgl_awal = request('tgl_awal');
    $tgl_akhir = request('tgl_akhir');

    if ($tgl_awal && $tgl_akhir) {
        $assets = AsetSekolah::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
        $fileName = "Inventaris {$tgl_awal} - {$tgl_akhir}.pdf";
    } else {
        $assets = AsetSekolah::all();
        $fileName = "Data Keseluruhan Inventaris.pdf";
    }

    $data = ['assets' => $assets];
    $pdf = Pdf::loadView('print.inventaris', $data);

    return $pdf->stream($fileName);
})->name('laporan.inventaris');
