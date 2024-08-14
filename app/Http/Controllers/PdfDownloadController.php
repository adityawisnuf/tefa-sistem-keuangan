<!-- <?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
// use Illuminate\Support\Facades\Storage;

// class PdfDownloadController extends Controller
// {
//    public function store(Request $request)
//    {
//         // Validasi input
//         $this->validate($request, [
//             'pdf_files' => 'required',
//             'pdf_files.*' => 'mimes:pdf',
//         ]);

//         // Periksa apakah ada file yang diunggah
//         if ($request->hasFile('pdf_files')) {
//             $pdf = PDFMerger::init();

//             foreach ($request->file('pdf_files') as $key => $value) {
//                 try {
//                     // Coba tambahkan PDF ke merger
//                     $pdf->addPDF($value->getPathname(), 'all');
//                 } catch (\Exception $e) {
//                     // Jika ada error, kembalikan response dengan pesan error
//                     return response()->json(['error' => 'Failed to process one or more PDFs due to unsupported compression technique.'], 400);
//                 }
//             }

//             // Tentukan nama file untuk PDF yang digabung
//             $filename = time() . '.pdf';
//             $pdf->merge();
//             $pdf->save(public_path($filename));

//             // Kembalikan file PDF yang digabungkan sebagai response
//             return response()->download(public_path($filename));
//         }

//         // Jika tidak ada file, kembalikan error
//         return response()->json(['error' => 'No files found'], 400);
//     }
// } -->
