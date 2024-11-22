<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrintPiutangTunggakanExcelController extends Controller
{
    public function exportExcel(Request $request)
    {
        // Filter data pembayaran
        $query = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('status', 1);
            })
            ->with(['pembayaran_siswa', 'pembayaran_kategori', 'siswa.kelas', 'siswa.orangtua']);

        if ($request->filled('nama_siswa')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('nama_depan', 'like', '%' . $request->nama_siswa . '%')
                  ->orWhere('nama_belakang', 'like', '%' . $request->nama_siswa . '%');
            });
        }

        if ($request->filled('kelas')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        if ($request->filled('jurusan')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('jurusan', $request->jurusan);
            });
        }

        $pembayaranList = $query->get();

        // Buat Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom
        $sheet->setCellValue('A1', 'No')
              ->setCellValue('B1', 'Nama Siswa')
              ->setCellValue('C1', 'Kelas')
              ->setCellValue('D1', 'Jurusan')
              ->setCellValue('E1', 'Telepon')
              ->setCellValue('F1', 'Orang Tua')
              ->setCellValue('G1', 'Piutang')
              ->setCellValue('H1', 'Tunggakan');

        // Styling Header
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4CAF50');
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setARGB('FFFFFF');

        // Data
        $row = 2;
        foreach ($pembayaranList as $key => $pembayaran) {
            $siswa = $pembayaran->siswa;

            $sheet->setCellValue('A' . $row, $key + 1);
            $sheet->setCellValue('B' . $row, $siswa->nama_depan . ' ' . $siswa->nama_belakang);
            $sheet->setCellValue('C' . $row, $siswa->kelas->kelas ?? 'N/A');
            $sheet->setCellValue('D' . $row, $siswa->kelas->jurusan ?? 'N/A');
            $sheet->setCellValue('E' . $row, $siswa->telepon ?? 'N/A');
            $sheet->setCellValue('F' . $row, $siswa->orangtua->nama ?? 'N/A');

            // Menghitung Piutang
            $piutangData = '';
            $tunggakanData = '';
            foreach ($pembayaran->pembayaran_siswa as $pembayaranSiswa) {
                $nominal = $pembayaranSiswa->nominal;
                $jatuhTempo = $pembayaranSiswa->jatuh_tempo;
                $status = $pembayaranSiswa->status;

                // Piutang (belum jatuh tempo)
                if ($status == 0 && $jatuhTempo > now()) {
                    $piutangData .= "Pembayaran ke-{$pembayaranSiswa->pembayaran_ke}: Rp" . number_format($nominal, 0, ',', '.') . "\n";
                }

                // Tunggakan (sudah lewat jatuh tempo)
                if ($status == 0 && $jatuhTempo <= now()) {
                    $tunggakanData .= "Pembayaran ke-{$pembayaranSiswa->pembayaran_ke}: Rp" . number_format($nominal, 0, ',', '.') . "\n";
                }
            }

            $sheet->setCellValue('G' . $row, $piutangData ?: 'Tidak Ada');
            $sheet->setCellValue('H' . $row, $tunggakanData ?: 'Tidak Ada');

            $row++;
        }

        // Atur lebar kolom
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Buat file Excel untuk diunduh
        $writer = new Xlsx($spreadsheet);
        $fileName = 'piutang_tunggakan.xlsx';

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$fileName\"");

        return $response;
    }
}
