<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sekolah;

class PrintPdfPiutangdanTunggakanController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::info("File PDF Piutang dan Tunggakan diakses oleh pengguna dengan IP: " . $request->ip());
    
        // Ambil data siswa dengan filter yang diminta
        $siswas = Siswa::with(['kelas', 'orangtua'])
            ->when($request->filled('nama_siswa') && $request->nama_siswa != "null", function ($query) use ($request) {
                $query->where('nama_depan', 'like', '%' . $request->nama_siswa . '%')
                      ->orWhere('nama_belakang', 'like', '%' . $request->nama_siswa . '%');
            })
            ->when($request->filled('kelas') && $request->kelas != "null", function ($query) use ($request) {
                $query->whereHas('kelas', function ($q) use ($request) {
                    $q->where('kelas', $request->kelas);
                });
            })
            ->when($request->filled('jurusan') && $request->jurusan != "null", function ($query) use ($request) {
                $query->whereHas('kelas', function ($q) use ($request) {
                    $q->where('jurusan', $request->jurusan);
                });
            })
            ->get();
    
        $result = [];
        $today = Carbon::now();
    
        foreach ($siswas as $siswa) {
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                    $query->where('status', 1); // Status aktif
                })
                ->where('siswa_id', $siswa->id)
                ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id)
                          ->with('pembayaran_siswa_cicilan');
                }, 'pembayaran_kategori'])
                ->get();
    
            $piutang = [];
            $tunggakan = [];
            $totalPiutang = 0;
            $totalTunggakan = 0;
    
            foreach ($pembayaranList as $pembayaran) {
                $pembayaran_siswa = $pembayaran->pembayaran_siswa->first();
                $nominal = $pembayaran->nominal;
                $status = 'Belum Lunas';
                $dueDate = Carbon::parse($pembayaran->due_date);
    
                if ($pembayaran_siswa && $pembayaran_siswa->status == 1) {
                    $status = 'Lunas';
                } else {
                    $piutang[] = [
                        'pembayaran_ke' => $pembayaran->pembayaran_kategori->nama ?? 'Nama Tidak Tersedia',
                        'nominal' => 'Rp' . number_format($nominal, 0, ',', '.'),
                        'status' => $status,
                        'due_date' => $dueDate->toDateString(),
                        'is_overdue' => $dueDate < $today,
                    ];
                    $totalPiutang += $nominal;
    
                    if ($dueDate < $today) {
                        $tunggakan[] = [
                            'pembayaran_ke' => $pembayaran->pembayaran_kategori->nama ?? 'Nama Tidak Tersedia',
                            'nominal' => 'Rp' . number_format($nominal, 0, ',', '.'),
                            'status' => $status,
                            'due_date' => $dueDate->toDateString(),
                        ];
                        $totalTunggakan += $nominal;
                    }
                }
            }
    
            if (!empty($piutang)) {
                $result[] = [
                    'nama_siswa' => $siswa->nama_depan . ' ' . ($siswa->nama_belakang ?? ''),
                    'kelas' => $siswa->kelas->kelas ?? '',
                    'jurusan' => $siswa->kelas->jurusan ?? '',
                    'telepon' => $siswa->telepon,
                    'orang_tua' => $siswa->orangtua->nama ?? null,
                    'piutang' => $piutang,
                    'tunggakan' => $tunggakan,
                    'total_piutang' => 'Rp' . number_format($totalPiutang, 0, ',', '.'),
                    'total_tunggakan' => 'Rp' . number_format($totalTunggakan, 0, ',', '.'),
                ];
            }
        }
    
        // Ambil data sekolah
        $sekolah = Sekolah::first();
    
        // Generate PDF dan kirim data ke view
        $pdf = Pdf::loadView('print.piutang_tunggakan', ['result' => $result, 'sekolah' => $sekolah]);
        return $pdf->stream('piutang_tunggakan.pdf');
    }
    
}