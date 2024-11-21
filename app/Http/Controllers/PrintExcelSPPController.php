<?php 
namespace App\Http\Controllers;

use App\Exports\PembayaranSiswaSPPExport;
use App\Models\Pembayaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PrintExcelSPPController extends Controller
{
    public function exportPembayaranSiswaToExcel(Request $request)
    {
        Log::info("File Excel diakses oleh pengguna dengan IP: " . $request->ip());

        $siswas = Siswa::with(['kelas', 'orangtua'])
        ->when($request->filled('nama_siswa') && $request->nama_siswa != "null", function ($query) use ($request) {
            $query->where('id', $request->nama_siswa);
        })
        ->when($request->filled('kelas') && $request->kelas != "null", function ($query) use ($request) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas);
            });
        })
        ->when($request->filled('jurusan') && $request->jurusan != "null", function ($query) use ($request) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('jurusan', $request->jurusan);
            });
        })
        ->get();

        $result = [];
        foreach ($siswas as $siswa) {
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('jenis_pembayaran', 1)
                    ->where('status', 1);
            })
            ->where('siswa_id', $siswa->id)
            ->with(['pembayaran_siswa', 'pembayaran_kategori'])
            ->get();

            $payments = [];
            foreach ($pembayaranList as $pembayaran) {
                $status = 'Belum Lunas';
                if ($pembayaran->pembayaran_siswa->isNotEmpty()) {
                    $status = $pembayaran->pembayaran_siswa->first()->status == 1 ? 'Lunas' : 'Belum Lunas';
                }

                $payments[] = [
                    'pembayaran_ke' => $pembayaran->pembayaran_ke,
                    'bulan' => $pembayaran->pembayaran_ke,
                    'nominal' => $pembayaran->nominal,
                    'status' => $status,
                ];
            }

            $result[] = [
                'nama_siswa' => $siswa->nama_depan . ' ' . $siswa->nama_belakang,
                'kelas' => $siswa->kelas->kelas ?? '',
                'jurusan' => $siswa->kelas->jurusan ?? '',
                'telepon' => $siswa->telepon,
                'orangtua' => $siswa->orangtua->nama ?? '',
                'sisa_tagihan' => $this->calculateSisaTagihan($pembayaranList),
                'payments' => $payments,
            ];
        }

        return Excel::download(new PembayaranSiswaSPPExport($result), 'Pembayaran-SPP-Siswa.xlsx');
    }

    private function calculateSisaTagihan($pembayaranList)
    {
        $totalPembayaran = 0;
        foreach ($pembayaranList as $pembayaran) {
            $totalPembayaran += $pembayaran->nominal;
        }

        $totalTagihan = 100000;
        return $totalTagihan - $totalPembayaran;
    }
}
