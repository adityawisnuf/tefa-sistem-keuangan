<?php

namespace App\Http\Controllers;

use App\Models\PendaftarDokumen;
use Illuminate\Http\Request;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfDownloadController extends Controller
{
    /**
     * Menggabungkan file PDF berdasarkan ID.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */public function mergePdfById($id)
{
    // Ambil data dari database untuk mendapatkan path file
    $pendaftarDokumen = PendaftarDokumen::findOrFail($id);

    // Path file PDF
    $files = [
        storage_path('app/' . $pendaftarDokumen->akte_kelahiran),
        storage_path('app/' . $pendaftarDokumen->kartu_keluarga),
        storage_path('app/' . $pendaftarDokumen->ijazah),
        storage_path('app/' . $pendaftarDokumen->raport),
    ];

    // Cek jika file ada
    foreach ($files as $file) {
        if (!file_exists($file) || !is_readable($file)) {
            return response()->json(['error' => 'One or more files not found or not readable.'], 404);
        }
    }

    // Inisialisasi Merger
    $merger = new Merger;

    try {
        // Tambahkan file PDF ke merger
        foreach ($files as $file) {
            try {
                // Cobalah untuk memperbaiki file PDF jika diperlukan
                $merger->addFile($file);
            } catch (\Exception $e) {
                Log::error('Failed to add file to merger', ['file' => $file, 'exception' => $e]);
                continue; // Lanjutkan dengan file lainnya
            }
        }

        // Gabungkan file PDF
        $createdPdf = $merger->merge();

        // Tentukan nama file untuk PDF yang digabung
        $filename = 'merged_' . time() . '.pdf';

        // Simpan PDF yang digabungkan ke penyimpanan publik
        Storage::disk('public')->put($filename, $createdPdf);

        // Kembalikan file PDF yang digabungkan sebagai response
        return response()->download(storage_path('app/public/' . $filename));
    } catch (\Exception $e) {
        Log::error('Failed to merge PDFs', ['exception' => $e]);
        return response()->json(['error' => 'Failed to merge PDFs.'], 500);
    }
}

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'pdf_files' => 'required',
            'pdf_files.*' => 'mimes:pdf',
        ]);

        $merger = new Merger;

        // Cek jika ada file yang diunggah
        if ($request->hasFile('pdf_files')) {
            foreach ($request->file('pdf_files') as $file) {
                try {
                    // Tambahkan file PDF ke merger
                    $merger->addFile($file->getPathname());
                } catch (\Exception $e) {
                    // Jika ada error, kembalikan response dengan pesan error
                    return response()->json(['error' => 'Failed to process one or more PDFs.'], 400);
                }
            }

            try {
                // Gabungkan file PDF
                $createdPdf = $merger->merge();

                // Simpan PDF yang digabungkan ke penyimpanan publik
                $filename = 'merged_' . time() . '.pdf';
                Storage::disk('public')->put($filename, $createdPdf);

                // Kembalikan file PDF yang digabungkan sebagai response
                return response()->download(storage_path('app/public/' . $filename));
            } catch (\Exception $e) {
                // Log error untuk debug
                Log::error('Failed to merge PDFs', ['exception' => $e]);

                return response()->json(['error' => 'Failed to merge PDFs.'], 500);
            }
        }

        return response()->json(['error' => 'No files found'], 400);
    }
}
