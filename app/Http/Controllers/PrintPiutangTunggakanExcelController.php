<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrintPiutangTunggakanExcelController extends Controller
{
    public function exportExcel(Request $request)
    {
        // Ambil data dengan filter yang sesuai
        $query = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('status', 1);
            })
            ->with(['pembayaran_siswa' => function ($query) {
                $query->with('pembayaran_siswa_cicilan');
            }, 'pembayaran_kategori', 'siswa.kelas']);

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

        // Buat Spreadsheet Excel baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'No')
              ->setCellValue('B1', 'Nama Siswa')
              ->setCellValue('C1', 'Kelas')
              ->setCellValue('D1', 'Jurusan')
              ->setCellValue('E1', 'Telepon')
              ->setCellValue('F1', 'Orang Tua')
              ->setCellValue('G1', 'Piutang')
              ->setCellValue('H1', 'Tunggakan');

        // Styling header
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4CAF50');
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setARGB('FFFFFF');

        // Isi data
        $row = 2;
        foreach ($pembayaranList as $key => $pembayaran) {
            $sheet->setCellValue('A' . $row, $key + 1);
            $sheet->setCellValue('B' . $row, $pembayaran->siswa->nama_depan . ' ' . $pembayaran->siswa->nama_belakang);
            $sheet->setCellValue('C' . $row, $pembayaran->siswa->kelas->kelas);
            $sheet->setCellValue('D' . $row, $pembayaran->siswa->kelas->jurusan);
            $sheet->setCellValue('E' . $row, $pembayaran->siswa->telepon);
            $sheet->setCellValue('F' . $row, $pembayaran->siswa->orangtua->nama ?? 'N/A');

            // Isi data Piutang dan Tunggakan
            $piutangData = '';
            foreach ($pembayaran->pembayaran_siswa as $piutang) {
                $piutangData .= "Pembayaran ke-{$piutang->pembayaran_ke}: Rp" . number_format($piutang->nominal, 0, ',', '.') . "\n";
            }
            $sheet->setCellValue('G' . $row, $piutangData);

            $tunggakanData = '';
            foreach ($pembayaran->pembayaran_siswa as $tunggakan) {
                $tunggakanData .= "Pembayaran ke-{$tunggakan->pembayaran_ke}: Rp" . number_format($tunggakan->nominal, 0, ',', '.') . "\n";
            }
            $sheet->setCellValue('H' . $row, $tunggakanData);

            $row++;
        }

        // Atur lebar kolom agar otomatis sesuai isi
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Download file sebagai respons
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
