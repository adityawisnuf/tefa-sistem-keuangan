<?php

namespace App\Http\Controllers;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Exports\TahunanExcelExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class PrintExcelTahunanController extends Controller
{
    public function exportExcel(Request $request)
    {
        Log::info("Yearly Excel accessed by user with IP: " . $request->ip());

        // Get students with filtering options if specified
        $siswas = Siswa::with(['kelas', 'orangtua'])
            ->when($request->filled('nama_siswa'), function ($query) use ($request) {
                $query->where('nama_depan', 'like', '%' . $request->nama_siswa . '%')
                    ->orWhere('nama_belakang', 'like', '%' . $request->nama_siswa . '%');
            })
            ->when($request->filled('kelas'), function ($query) use ($request) {
                $query->whereHas('kelas', fn($q) => $q->where('kelas', $request->kelas));
            })
            ->when($request->filled('jurusan'), function ($query) use ($request) {
                $query->whereHas('kelas', fn($q) => $q->where('jurusan', $request->jurusan));
            })
            ->get();

        $result = [];

        // Gather yearly payment data for each student
        foreach ($siswas as $siswa) {
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('jenis_pembayaran', 2)  // Yearly payment type
                    ->where('status', 1); // Active status
            })
            ->where('siswa_id', $siswa->id)
            ->with(['pembayaran_siswa', 'pembayaran_kategori'])
            ->get();

            $payments = [];

            foreach ($pembayaranList as $pembayaran) {
                $status = $pembayaran->pembayaran_siswa->isNotEmpty() 
                    ? ($pembayaran->pembayaran_siswa->first()->status == 1 ? 'Lunas' : 'Belum Lunas')
                    : 'Belum Lunas';

                $payments[] = [
                    'pembayaran_ke' => $pembayaran->pembayaran_ke,
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

        // Render the view into an Excel file, pass result instead of data
        return Excel::download(new TahunanExcelExport($result), 'data_pembayaran_tahunan.xlsx');
    }

    // Example function for calculating remaining balance, implement as needed
    private function calculateSisaTagihan($pembayaranList)
    {
        $totalBayar = 0;
        foreach ($pembayaranList as $pembayaran) {
            $totalBayar += $pembayaran->nominal;
        }
        return $totalBayar; // Adjust according to your logic
    }
}
