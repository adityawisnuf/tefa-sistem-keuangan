<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\PendaftarDokumen;
use setasign\Fpdi\Fpdi;

class PdfDownloadController extends Controller
{
    public function downloadPDF($id)
    {
        set_time_limit(300); // 5 menit
        $dokumen = PendaftarDokumen::find($id);
        if (!$dokumen) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // Generate individual PDFs and store them temporarily
        $pdfs = [];
        foreach (['akte_kelahiran', 'kartu_keluarga', 'ijazah', 'raport'] as $field) {
            $imageUrl = url('storage/dokumen/' . basename($dokumen->$field));
            $htmlContent = '<!DOCTYPE html>
            <html>
            <head>
                <title>' . $field . '</title>
            </head>
            <body>
                <img src="' . $imageUrl . '" alt="' . $field . '"/>
            </body>
            </html>';

            $pdf = Pdf::loadHTML($htmlContent);
            $filename = 'temp_' . $field . '_' . $id . '.pdf';
            $pdfPath = storage_path('app/public/temp/' . $filename);
            Storage::put('temp/' . $filename, $pdf->output()); // Store in 'temp/' folder
            $pdfs[] = $pdfPath;
        }

        // Merge PDFs using Fpdi and FPDF
        $fpdi = new Fpdi();
        foreach ($pdfs as $pdfFile) {
            $pageCount = $fpdi->setSourceFile($pdfFile);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplIdx = $fpdi->importPage($pageNo);
                $fpdi->addPage();
                $fpdi->useTemplate($tplIdx);
            }
        }

        // Create the final PDF
        $mergedPdfPath = storage_path('app/public/pdf/merged_dokumen_' . $id . '.pdf');
        $fpdi->Output('F', $mergedPdfPath);

        // Delete temporary PDFs
        foreach ($pdfs as $pdfFile) {
            Storage::delete('temp/' . basename($pdfFile));
        }

        // Download the merged PDF
        return response()->download($mergedPdfPath);
    }
}
