<?php
namespace App\Http\Controllers;

use App\Http\Requests\PpdbRequest;
use App\Models\PembayaranDuitku;
use App\Models\Pendaftar;
use App\Models\Ppdb;
use App\Models\PendaftarDokumen;
use App\Models\PendaftarAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PpdbController extends Controller
{

    public function getTotalPendaftar()
{
    try {
        $totalPendaftar = Ppdb::count();

        return response()->json([
            'success' => true,
            'total_pendaftar' => $totalPendaftar
        ]);
    } catch (\Exception $e) {
        Log::error('Error getting total pendaftar:', [
            'exception' => $e->getMessage(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve total pendaftar. Please try again later.'
        ], 500);
    }
}







    public function store(PpdbRequest $request)
    {
        DB::beginTransaction();

        try {
            // Generate a unique merchantOrderId
            $merchantOrderId = Str::uuid()->toString();
            $akteKelahiranPath = $request->file('akte_kelahiran')->store('documents');
            $kartuKeluargaPath = $request->file('kartu_keluarga')->store('documents');
            $ijazahPath = $request->file('ijazah')->store('documents');
            $raportPath = $request->file('raport')->store('documents');

            $dataUserResponse = $request->only([
                'nama_depan',
                'nama_belakang',
                'jenis_kelamin',
                'nik',
                'email',
                'nisn',
                'tempat_lahir',
                'tgl_lahir',
                'alamat',
                'village_id',
                'nama_ayah',
                'nama_ibu',
                'tgl_lahir_ayah',
                'tgl_lahir_ibu',
                'sekolah_asal',
                'tahun_lulus',
                'jurusan_tujuan'
            ]);

            // Add file paths to the user data
            $dataUserResponse['akte_kelahiran'] = $akteKelahiranPath;
            $dataUserResponse['kartu_keluarga'] = $kartuKeluargaPath;
            $dataUserResponse['ijazah'] = $ijazahPath;
            $dataUserResponse['raport'] = $raportPath;

            // Create `PembayaranDuitku` record with encoded user data
            $pembayaranDuitku = PembayaranDuitku::create([
                'merchant_order_id' => $merchantOrderId,
                'status' => 'pending',
                'data_user_response' => json_encode($dataUserResponse),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Data berhasil disimpan di PembayaranDuitku!',
                'pendaftar' => $pembayaranDuitku->toArray(),  // Use toArray() to check serialized data
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Pendaftaran gagal:', [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat pendaftaran. Silakan coba lagi.',
            ], 500);
        }
    }


    public function downloadDocuments($id)
    {
        try {
            // Mengambil data dokumen berdasarkan ID
            $pendaftarDokumen = PendaftarDokumen::findOrFail($id);

            // Mengambil data pendaftar berdasarkan ppdb_id yang terdapat pada dokumen
            $pendaftar = Pendaftar::where('ppdb_id', $pendaftarDokumen->ppdb_id)->firstOrFail();

            // Mengambil nama depan dan nama belakang dari pendaftar
            $namaDepan = $pendaftar->nama_depan;
            $namaBelakang = $pendaftar->nama_belakang;

            $folderName = $namaDepan . '_' . $namaBelakang;
            $zipFileName = $folderName . '_dokumen_' . $id . '.zip';

            $files = [
                'akte_kelahiran' => $pendaftarDokumen->akte_kelahiran,
                'kartu_keluarga' => $pendaftarDokumen->kartu_keluarga,
                'ijazah' => $pendaftarDokumen->ijazah,
                'raport' => $pendaftarDokumen->raport,
            ];

            $zip = new ZipArchive();

            if ($zip->open(storage_path($zipFileName), ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                foreach ($files as $type => $file) {
                    if (Storage::exists($file)) {
                        // Create a new name for the file using the first and last name plus the document type
                        $newFileName = $folderName . '_' . $type . '.' . pathinfo($file, PATHINFO_EXTENSION);
                        $filePath = storage_path('app/' . $file);
                        $zip->addFile($filePath, $newFileName);
                    }
                }
                $zip->close();
            }

            // Download the created ZIP file
            return response()->download(storage_path($zipFileName))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error while downloading documents for ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }



    public function updateStatus(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'id' => 'required|exists:ppdb,id',
            'status' => 'required|integer|'
        ]);

        $ppdbId = $validated['id'];
        $status = $validated['status'];

        try {
            $ppdb = Ppdb::findOrFail($ppdbId);
            $ppdb->status = $status;
            $ppdb->save();

            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'data' => $ppdb
            ]);
        } catch (\Exception $e) {
            // Handle exception (e.g., log it)
            Log::error('Status update failed:', [
                'exception' => $e->getMessage(),
                'id' => $ppdbId,
                'status' => $status,
            ]);

            // Return an error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again later.'
            ], 500);
        }
    }
}
