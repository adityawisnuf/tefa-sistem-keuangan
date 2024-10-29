<?php

use App\Http\Controllers\Api\AnggaranController;
use App\Http\Controllers\Api\AsetSekolahController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\PengeluaranExcelController;
use App\Http\Controllers\Api\PrintExcelController;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Anggaran;
use App\Models\AsetSekolah;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Support\Facades\Log;

// Register and Login Routes
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::get('select/principal', fn() => response()->json([
    "sucess" => true,
    "message" => "Berhasil mendapatkan kepala sekolah",
    "data" => User::where('role', 'Kepala Sekolah')->get()
]));

Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    // Route untuk mendapatkan data siswa
    Route::get('/select/siswa', function () {
        Log::info('Route select/siswa accessed'); // Tambahkan log
        $siswaData = Siswa::all();
        return response()->json([
            'message' => 'Berhasil mendapatkan data siswa',
            'success' => true,
            'data' => $siswaData->map(function ($siswa) {
                return [
                    'value' => $siswa->id,
                    'label' => $siswa->nama_depan . " " . $siswa->nama_belakang
                ];
            })
        ]);
    });

    Route::get('/select/kelas', function () {
        $kelasData = Kelas::all();
        return response()->json([
            'message' => 'Berhasil mendapatkan data kelas',
            'success' => true,
            'data' => $kelasData->map(function ($kelas) {
                return [
                    'value' => $kelas->id,
                    'label' => $kelas->kelas
                ];
            })
        ]);
    });

    // Route untuk mendapatkan data anggaran
    Route::get('/select/anggaran', function () {
        $table = Anggaran::all();
        return response()->json([
            'message' => 'Berhasil mendapatkan data anggaran',
            'success' => true,
            'data' => $table->map(function ($anggaran) {
                return [
                    'value' => $anggaran->id,
                    'label' => $anggaran->nama_anggaran
                ];
            })
        ]);
    });


    // Role: Admin
Route::group([
    'middleware' => ['checkrole:Admin'],
    'prefix' => 'Admin'
], function () {
    // CRUD Routes
    Route::post('/anggaran', [AnggaranController::class, 'store']);
    Route::get('/anggaran', [AnggaranController::class, 'index']);
    Route::get('/anggaran/chart-data', [AnggaranController::class, 'getAnggaranData']);
    Route::get('/anggaran/total-rencana-anggaran', [AnggaranController::class, 'getTotalRencanaAnggaran']);
    Route::get('/anggaran/total-realisasi-anggaran', [AnggaranController::class, 'getTotalRealisasiAnggaran']);
    Route::patch('/anggaran/{anggaran}', [AnggaranController::class, 'update']);

    // Laporan Anggaran
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

        $data = ['anggarans' => $anggaran,
        'sekolah'=>Sekolah::first()
    ];
        $pdf = Pdf::loadView('print.anggaran', $data);

        return $pdf->stream($fileName);
    })->name('laporan.anggaran');

     // Laporan Deviasi
     Route::get('/laporan/deviasi', [AnggaranController::class, 'printDeviasi'])->name('laporan.deviasi');
});

// Role: Bendahara
Route::group([
    'middleware' => ['checkrole:Bendahara'],
    'prefix' => 'Bendahara'
], function () {
    // CRUD Routes
    Route::post('/anggaran', [AnggaranController::class, 'store']);
    Route::get('/anggaran', [AnggaranController::class, 'index']);
    Route::get('/anggaran/chart-data', [AnggaranController::class, 'getAnggaranData']);
    Route::get('/anggaran/total-rencana-anggaran', [AnggaranController::class, 'getTotalRencanaAnggaran']);
    Route::get('/anggaran/total-realisasi-anggaran', [AnggaranController::class, 'getTotalRealisasiAnggaran']);
    Route::patch('/anggaran/{anggaran}', [AnggaranController::class, 'update']);
    //Route::delete('/anggaran/{anggaran}', [AnggaranController::class, 'destroy']);

    // Laporan Anggaran
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

        $data = ['anggarans' => $anggaran,
        'sekolah'=>Sekolah::first()
    ];
        $pdf = Pdf::loadView('print.anggaran', $data);

        return $pdf->stream($fileName);
    })->name('laporan.anggaran');
    Route::get('/laporan/deviasi', [AnggaranController::class, 'printDeviasi'])->name('laporan.deviasi');
});

    // Routes untuk role "Bendahara"
    Route::middleware(['checkrole:Bendahara'])->prefix('Bendahara')->group(function () {
        Route::post('/pemasukan', [PembayaranController::class, 'index']); 
        Route::post('/pengeluaran', [PengeluaranController::class, 'index']);
        Route::get('/export-pengeluaran', [PengeluaranExcelController::class, 'exportPengeluaran'])
            ->name('pengeluaran.exportExcel');
        Route::get('/pembayaran/export-excel', [PrintExcelController::class, 'exportExcel'])
            ->name('pembayaran.exportExcel');

        // Laporan pembayaran PDF
        Route::get('/laporan/pembayaran', [PembayaranController::class, 'report'])->name('laporan.pembayaran');

      
         // Laporan pengeluaran PDF
         Route::get('/laporan/pengeluaran', [PengeluaranController::class, 'report'])->name('laporan.pengeluaran');
        });

    // Routes untuk role "Kepala Sekolah"
    Route::middleware(['checkrole:Kepala Sekolah'])->prefix('Kepala Sekolah')->group(function () {
        Route::post('/pemasukan', [PembayaranController::class, 'index']); 
        Route::post('/pengeluaran', [PengeluaranController::class, 'index']); 
        Route::get('/export-pengeluaran', [PengeluaranExcelController::class, 'exportPengeluaran'])
        ->name('pengeluaran.exportExcel');
        Route::get('/pembayaran/export-excel', [PrintExcelController::class, 'exportExcel'])
            ->name('pembayaran.exportExcel');

        // Laporan pembayaran PDF
        Route::get('/laporan/pembayaran', [PembayaranController::class, 'report'])->name('laporan.pembayaran');
        
         // Laporan pengeluaran PDF
         Route::get('/laporan/pengeluaran', [PengeluaranController::class, 'report'])->name('laporan.pengeluaran');
   
    });

// Role: Kepala Sekolah
Route::group([
    'middleware' => ['checkrole:Kepala Sekolah'],
    'prefix' => 'Kepala Sekolah'
], function () {
    // Read Routes
    Route::post('/anggaran', [AnggaranController::class, 'store']);
    Route::get('/anggaran', [AnggaranController::class, 'index']);
    Route::patch('/anggaran/{anggaran}', [AnggaranController::class, 'update']);
    Route::delete('/anggaran/{anggaran}', [AnggaranController::class, 'destroy']);
    Route::get('/anggaran/chart-data', [AnggaranController::class, 'getAnggaranData']);
    Route::get('/anggaran/total-rencana-anggaran', [AnggaranController::class, 'getTotalRencanaAnggaran']);
    Route::get('/anggaran/total-realisasi-anggaran', [AnggaranController::class, 'getTotalRealisasiAnggaran']);
    Route::delete('/anggaran/{anggaran}', [AnggaranController::class, 'destroy']);

    // Laporan Anggaran
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

        $data = ['anggarans' => $anggaran,
        'sekolah'=>Sekolah::first()
    ];
        $pdf = Pdf::loadView('print.anggaran', $data);

        return $pdf->stream($fileName);
    })->name('laporan.anggaran');

    Route::get('/laporan/deviasi', [AnggaranController::class, 'printDeviasi'])->name('laporan.deviasi');
});

// ROLE: Admin
Route::group([
    'middleware' => ['auth:api', 'checkrole:Admin'],
    'prefix' => "Admin"
], function () {
    // CRUD
    Route::post('/aset', [AsetSekolahController::class, 'store']);
    Route::get('/aset', [AsetSekolahController::class, 'index']);
    Route::patch('/aset/{aset}/', [AsetSekolahController::class, 'update']);
    Route::delete('/aset/{aset}', [AsetSekolahController::class, 'destroy']);

    Route::get('/laporan/inventaris', function () {
        $tgl_awal = request('tgl_awal');
        $tgl_akhir = request('tgl_akhir');

        if ($tgl_awal && $tgl_akhir) {
            // $asset = AsetSekolah::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
            $fileName = "Aset {$tgl_awal} - {$tgl_akhir}.pdf";
        } else {
            $asset = AsetSekolah::all();
            $fileName = "Data Keseluruhan Asset.pdf";
        }

        $data = ['assets' => $asset,
    'sekolah'=>Sekolah::first()
    ];
        $pdf = Pdf::loadView('print.inventaris', $data);

        return $pdf->stream($fileName);
    })->name('laporan.inventaris');
});

// ROLE: Bendahara
Route::group([
    'middleware' => ['auth:api', 'checkrole:Bendahara'],
    'prefix' => 'Bendahara'
], function () {

    Route::get('/aset', [AsetSekolahController::class, 'index']);

    Route::get('/laporan/inventaris', function () {
        $tgl_awal = request('tgl_awal');
        $tgl_akhir = request('tgl_akhir');

        if ($tgl_awal && $tgl_akhir) {
            $asset = AsetSekolah::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
            $fileName = "Aset {$tgl_awal} - {$tgl_akhir}.pdf";
        } else {
            $asset = AsetSekolah::all();
            $fileName = "Data Keseluruhan Asset.pdf";
        }

        $data = ['assets' => $asset,
        'sekolah'=>Sekolah::first()
    ];
        $pdf = Pdf::loadView('print.inventaris', $data);

        return $pdf->stream($fileName);
    })->name('laporan.inventaris');
   
});

// Role Kepala Sekolah
Route::group([
    'middleware' => ['auth:api', 'checkrole:Kepala Sekolah'],
    'prefix' => 'Kepala Sekolah'
], function () {

    Route::get('/aset', [AsetSekolahController::class, 'index']);

    Route::get('/laporan/inventaris', function () {
        $tgl_awal = request('tgl_awal');
        $tgl_akhir = request('tgl_akhir');

        if ($tgl_awal && $tgl_akhir) {
            $asset = AsetSekolah::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
            $fileName = "Aset {$tgl_awal} - {$tgl_akhir}.pdf";
        } else {
            $asset = AsetSekolah::all();
            $fileName = "Data Keseluruhan Asset.pdf";
        }

        $data = ['assets' => $asset,
        'sekolah'=>Sekolah::first()
    ];
        $pdf = Pdf::loadView('print.inventaris', $data);

        return $pdf->stream($fileName);
    })->name('laporan.inventaris');
});

});