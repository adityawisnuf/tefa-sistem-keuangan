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

        // Mulai query dengan pembayaran aktif
        $query = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('status', 1);
            })
            ->with(['pembayaran_siswa' => function ($query) {
                $query->with('pembayaran_siswa_cicilan');
            }, 'pembayaran_kategori', 'siswa.kelas']);

        // Filter berdasarkan nama siswa
        if ($request->filled('nama_siswa')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('nama_depan', 'like', '%' . $request->nama_siswa . '%')
                  ->orWhere('nama_belakang', 'like', '%' . $request->nama_siswa . '%');
            });
        }

        // Filter berdasarkan kelas
        if ($request->filled('kelas')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        // Filter berdasarkan jurusan
        if ($request->filled('jurusan')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('jurusan', $request->jurusan);
            });
        }

        // Dapatkan semua data pembayaran setelah filter
        $pembayaranList = $query->get();
        $dataSiswa = [];
        $today = Carbon::now();

        foreach ($pembayaranList as $pembayaran) {
            $pembayaran_siswa = $pembayaran->pembayaran_siswa->first();
            $nominal = $pembayaran->nominal;
            $siswa = $pembayaran->siswa;
            $dueDate = Carbon::parse($pembayaran->due_date);

            $status = $pembayaran_siswa ? ($pembayaran_siswa->status == 1 ? 'Lunas' : 'Belum Lunas') : 'Belum Lunas';

            if (!$pembayaran_siswa || $pembayaran_siswa->status != 1) {
                if (!isset($dataSiswa[$siswa->id])) {
                    $dataSiswa[$siswa->id] = [
                        'nama_siswa' => $siswa->nama_depan . ($siswa->nama_belakang ? ' ' . $siswa->nama_belakang : ''),
                        'kelas' => $siswa->kelas->kelas,
                        'jurusan' => $siswa->kelas->jurusan,
                        'telepon' => $siswa->telepon,
                        'orang_tua' => $siswa->orangtua->nama ?? null,
                        'piutang' => [],
                        'tunggakan' => [],
                    ];
                }

                $pembayaranDetail = [
                    'pembayaran_ke' => $pembayaran->pembayaran_ke,
                    'nominal' => 'Rp' . number_format($nominal, 0, ',', '.'),
                    'status' => $status,
                    'due_date' => $dueDate->toDateString(),
                    'is_overdue' => $dueDate < $today,
                ];

                $dataSiswa[$siswa->id]['piutang'][] = $pembayaranDetail;

                if ($dueDate < $today) {
                    $dataSiswa[$siswa->id]['tunggakan'][] = $pembayaranDetail;
                }
            }
        }

        // Hitung total piutang dan tunggakan
        foreach ($dataSiswa as $siswaId => $data) {
            $dataSiswa[$siswaId]['total_piutang'] = count($data['piutang']);
            $dataSiswa[$siswaId]['total_tunggakan'] = count($data['tunggakan']);
        }

        // Ambil data sekolah
        $sekolah = Sekolah::first();

        // Generate PDF dan kirim data ke view
        $pdf = Pdf::loadView('print.piutang_tunggakan', ['dataSiswa' => $dataSiswa, 'sekolah' => $sekolah]);
        return $pdf->stream('piutang_tunggakan.pdf');
    }
}
