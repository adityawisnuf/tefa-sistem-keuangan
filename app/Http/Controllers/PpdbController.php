<?php
namespace App\Http\Controllers;

use App\Http\Requests\PpdbRequest;
use App\Models\Pendaftar;
use App\Models\Ppdb;
use App\Models\PendaftarDokumen;
use App\Models\PendaftarAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PpdbController extends Controller
{
    public function store(PpdbRequest $request)
    {
        DB::beginTransaction();

        try {
            // Mendapatkan ID pengguna yang sedang login
            $userId = Auth::id();

            // Retrieve ppdb_id from session
            $ppdbId = $request->session()->get('ppdb_id');

            // Fetch the ppdb record using the id from the session and verify ownership
            $ppdb = Ppdb::where('id', $ppdbId)
                         ->where('user_id', $userId)
                         ->firstOrFail();

            // Create `pendaftar`
            $pendaftar = Pendaftar::create([
                'ppdb_id'   => $ppdb->id,
                'nama_depan' => $request->input('nama_depan'),
                'nama_belakang' => $request->input('nama_belakang'),
                'jenis_kelamin' => $request->input('jenis_kelamin'),
                'nik' => $request->input('nik'),
                'email' => $request->input('email'),
                'nisn' => $request->input('nisn'),
                'tempat_lahir' => $request->input('tempat_lahir'),
                'tgl_lahir' => $request->input('tgl_lahir'),
                'alamat' => $request->input('alamat'),
                'village_id' => $request->input('village_id'),
                'nama_ayah' => $request->input('nama_ayah'),
                'nama_ibu' => $request->input('nama_ibu'),
                'tgl_lahir_ayah' => $request->input('tgl_lahir_ayah'),
                'tgl_lahir_ibu' => $request->input('tgl_lahir_ibu'),
            ]);

            // Simpan data dokumen
            PendaftarDokumen::create([
                'ppdb_id' => $ppdb->id,
                'akte_kelahiran' => $request->file('akte_kelahiran')->store('documents'),
                'kartu_keluarga' => $request->file('kartu_keluarga')->store('documents'),
                'ijazah' => $request->file('ijazah')->store('documents'),
                'raport' => $request->file('raport')->store('documents'),
            ]);

            // Simpan data akademik
            PendaftarAkademik::create([
                'ppdb_id' => $ppdb->id,
                'sekolah_asal' => $request->sekolah_asal,
                'tahun_lulus' => $request->tahun_lulus,
                'jurusan_tujuan' => $request->jurusan_tujuan,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pendaftaran berhasil!',
                'pendaftar' => $pendaftar,
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
    // Mengambil data dokumen berdasarkan ID
    $pendaftarDokumen = PendaftarDokumen::findOrFail($id);

    // Mengambil data pendaftar berdasarkan ppdb_id yang terdapat pada dokumen
    $pendaftar = Pendaftar::where('ppdb_id', $pendaftarDokumen->ppdb_id)->firstOrFail();

    // Mengambil nama depan dan nama belakang dari pendaftar
    $namaDepan = $pendaftar->nama_depan;
    $namaBelakang = $pendaftar->nama_belakang;

    // Membuat nama folder menggunakan nama depan dan nama belakang
    $folderName = $namaDepan . '_' . $namaBelakang;
    $zipFileName = $folderName . '_dokumen_' . $id . '.zip';

    $files = [
        'akte_kelahiran' => $pendaftarDokumen->akte_kelahiran,
        'kartu_keluarga' => $pendaftarDokumen->kartu_keluarga,
        'ijazah' => $pendaftarDokumen->ijazah,
        'raport' => $pendaftarDokumen->raport,
    ];

    $zip = new \ZipArchive();

    if ($zip->open(storage_path($zipFileName), \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
        foreach ($files as $name => $file) {
            if (Storage::exists($file)) {
                $filePath = storage_path('app/' . $file);
                $zip->addFile($filePath, basename($filePath));
            }
        }
        $zip->close();
    }

    return response()->download(storage_path($zipFileName))->deleteFileAfterSend(true);
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
