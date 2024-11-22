<?php


use App\Http\Controllers\Api\AnggaranController;
use App\Http\Controllers\Api\AsetSekolahController;
use App\Http\Controllers\Api\PembayaranBukuKasController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PembayaranKategoriController;
use App\Http\Controllers\Api\PembayaranSiswaController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\PengeluaranExcelController;
use App\Http\Controllers\Api\PrintExcelController;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PembayaranManualController;
use App\Http\Controllers\PrintExcelSPPController;
use App\Http\Controllers\PrintExcelTahunanController;
use App\Http\Controllers\PrintInventarisController;
use App\Http\Controllers\PrintPdfPiutangdanTunggakanController;
use App\Http\Controllers\PrintPdfSPPController;
use App\Http\Controllers\PrintPdfTahunanPemasukanController;
use App\Http\Controllers\PrintPiutangTunggakanExcelController;
use App\Http\Controllers\PrintSPPExcelController;
use App\Http\Controllers\SendPaymentSuccesController;
use App\Models\Anggaran;
use App\Models\AsetSekolah;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\Sekolah;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('duitku/callback', [PembayaranController::class, 'duitkuCallbackHandler'])->name('payment.transaction.callback');
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::get('select/principal', fn() => response()->json([
    "sucess" => true,
    "message" => "Berhasil mendapatkan kepala sekolah",
    "data" => User::where('role', 'Kepala Sekolah')->get()
]));

Route::middleware('auth:api')->group(function () {
    Route::post('/send-whatsapp', [SendPaymentSuccesController::class, 'sendMessage']);
    Route::get('/get-groups', [SendPaymentSuccesController::class, 'getGroups']);
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::prefix('select')->group(function () {
        Route::get('/siswa', function () {
            $siswaData = Siswa::all();

            return response()->json([
                'message' => 'Berhasil mendapatkan data siswa',
                'success' => true,
                'data' => $siswaData->map(function ($siswa) {
                    return [
                        'value' => $siswa->id,
                        'label' => $siswa->nama_depan.' '.$siswa->nama_belakang,
                    ];
                }),
            ]);
        });

        Route::get('/kelas', function () {
            $kelasData = Kelas::all();

            return response()->json([
                'message' => 'Berhasil mendapatkan data kelas',
                'success' => true,
                'data' => $kelasData->map(function ($kelas) {
                    return [
                        'value' => $kelas->id,
                        'label' => $kelas->kelas,
                    ];
                }),
            ]);
        });

        Route::get('/jurusan', function () {
            Log::info('Route select/jurusan accessed');
            $jurusanData = Kelas::all();

            return response()->json([
                'message' => 'Berhasil mendapatkan data jurusan',
                'success' => true,
                'data' => $jurusanData->map(function ($kelas) {
                    return [
                        'value' => $kelas->jurusan,
                        'label' => $kelas->jurusan,
                    ];
                }),
            ]);
        });
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
    

    Route::prefix('payment')->group(function () {
        Route::get('/me', [PembayaranController::class, 'getCurrent'])->name('payment.transaction.getMonth');
        Route::get('/me/yearly', [PembayaranController::class, 'getCurrentYear'])->name('payment.transaction.getYear');
        Route::get('/yearly', [PembayaranController::class, 'getRiwayatTahunan'])->name('payment.transaction.getYear');
        Route::get('/', [PembayaranController::class, 'getRiwayat'])->name('payment.transaction.request');
        Route::get('methods/{id}', [PembayaranController::class, 'getPaymentMethod'])->name('payment.methods');
        Route::post('transaction/request', [PembayaranController::class, 'requestTransaksi'])->name('payment.transaction.request');
        Route::post('installment/request', [PembayaranController::class, 'requestTransaksiCicilan'])->name('payment.transaction.request');
        Route::post('cancel/{merchant_order_id}', [PembayaranController::class, 'batalTransaksi'])->name('payment.transaction.request');
        Route::post('assign', function (Request $request) {
            $data = $request->validate([
                'pembayaran_kategori_id' => 'required|exists:pembayaran_kategori,id',
                'siswa_id' => 'nullable',
                'kelas_id' => 'nullable|exists:kelas,id',
                'nominal' => 'required|numeric',
            ]);

            // Start building the query
            $query = Pembayaran::where('pembayaran_kategori_id', $data['pembayaran_kategori_id'])
                ->where('nominal', $data['nominal']);

            // Add siswa_id to the query if it is present
            if (! empty($data['siswa_id'])) {
                $query->where('siswa_id', $data['siswa_id']);
            }

            // Add kelas_id to the query if it is present
            if (! empty($data['kelas_id'])) {
                $query->where('kelas_id', $data['kelas_id']);
            }

            // Check if a matching Pembayaran already exists
            $existingPembayaran = $query->first();

            if ($existingPembayaran) {
                return response()->json(['success' => false, 'message' => 'Pembayaran with the same specifications already exists'], 400);
            }

            // If no existing Pembayaran, create a new one
            $data['status'] = true;
            $pembayaran = Pembayaran::create($data);

            return response()->json(['success' => true, 'message' => 'Berhasil membuat pembayaran baru', 'data' => $pembayaran]);
        })->name('payment.assign');
    });
    
    //Role: ADMIN
    Route::middleware('checkrole:Admin')->prefix('Admin')->group(function () {
        Route::get('students', [PembayaranManualController::class, 'getStudents']);
        Route::get('payment/list', [PembayaranManualController::class, 'getStudentPaymentList']);
        Route::post('payment/add', [PembayaranManualController::class, 'payManually']);

        Route::get('pembayaran-kategori', [PembayaranKategoriController::class, 'index']);
        Route::post('pembayaran-kategori', [PembayaranKategoriController::class, 'store']);
        Route::patch('pembayaran-kategori/{id}', [PembayaranKategoriController::class, 'update']);
        Route::delete('pembayaran-kategori/{id}', [PembayaranKategoriController::class, 'destroy']);
    });


 // Role: BENDAHARA
 Route::middleware('checkrole:Bendahara')->prefix('Bendahara')->group(function () {
    Route::post('/siswa', [SiswaController::class, 'index']);
    Route::post('/get/payment/siswa', [PembayaranController::class, 'getPembayaran']);
    Route::post('/get/payment/siswa/tahunan', [PembayaranController::class, 'getPembayaranTahunan']);
    Route::post('/piutang-tunggakan', [PembayaranController::class, 'getPiutangTunggakan']);
    Route::get('/laporan/spp', [PrintPdfSPPController::class, 'cetakSiswaPembayaran']);
    Route::get('/laporan/pemasukan-tahunan', [PrintPdfTahunanPemasukanController::class, '__invoke']);
    Route::get('/laporan/UtangPiutang', [PrintPdfPiutangdanTunggakanController::class, '__invoke']);
    Route::get('/excel-tahunan', [PrintExcelTahunanController::class, 'exportExcel']);
    Route::get('/excel-spp', [PrintExcelSPPController::class, 'exportPembayaranSiswaToExcel']);
    Route::get('/excel-piutang-tunggakan', [PrintPiutangTunggakanExcelController::class, 'exportExcel']);

    });

// Role: SISWA
Route::middleware('checkrole:Siswa')->prefix('siswa')->group(function () {
    Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index']);
    Route::patch('/pembayaran-siswa/{id}', [PembayaranSiswaController::class, 'update']);
    Route::get('/riwayat-pembayaran', [PembayaranSiswaController::class, 'riwayatPembayaran']);
    Route::get('/riwayat-tagihan', [PembayaranSiswaController::class, 'riwayatTagihan']);
    Route::get('/pembayaran/notifications', [PembayaranKategoriController::class, 'notifications']);
    Route::get('/peringatan-jatuh-tempo', [PembayaranKategoriController::class, 'peringatanJatuhTempo']);
    Route::post('/send-whatsapp', [SendPaymentSuccesController::class, 'sendMessage']);
    Route::get('/get-groups', [SendPaymentSuccesController::class, 'getGroups']);
    Route::get('/send-payment-reminder', [SendPaymentSuccesController::class, 'sendPaymentReminder']);
});

// Role: ORANG TUA
Route::middleware('checkrole:Orang Tua')->prefix('orangtua')->group(function () {
    Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index']);
    Route::post('/pembayaran-siswa/{id}/bayar', [PembayaranSiswaController::class, 'bayar']);
    Route::get('/riwayat-pembayaran', [PembayaranSiswaController::class, 'riwayatPembayaran']);
    Route::get('/riwayat-tagihan', [PembayaranSiswaController::class, 'riwayatTagihan']);
    Route::get('/pembayaran/notifications', [PembayaranKategoriController::class, 'notifications']);
    Route::get('/peringatan-jatuh-tempo', [PembayaranKategoriController::class, 'peringatanJatuhTempo']);
    Route::post('/send-whatsapp', [SendPaymentSuccesController::class, 'sendMessage']);
    Route::get('/get-groups', [SendPaymentSuccesController::class, 'getGroups']);
    Route::get('/send-payment-reminder', [SendPaymentSuccesController::class, 'sendPaymentReminder']);


  
   

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
        Route::post('/pemasukan', [PembayaranBukuKasController::class, 'index']); 
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
        Route::post('/pemasukan', [PembayaranBukuKasController::class, 'index']); 
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
});