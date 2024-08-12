<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
{
    // Get all Kelas
    public function index()
    {
        $kelas = Kelas::all();

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditampilkan',
            'data' => $kelas
        ]);
    }

    // Get single Kelas by ID
    public function show($id)
    {
        $kelas = Kelas::find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditampilkan',
            'data' => $kelas
        ]);
    }

    // Create a new Kelas
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sekolah_id' => 'required|exists:sekolah,id',
            'jurusan' => 'required|string',
            'kelas' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        $kelas = Kelas::create([
            'sekolah_id' => $request->sekolah_id,
            'jurusan' => $request->jurusan,
            'kelas' => $request->kelas
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditambahkan',
            'data' => $kelas
        ]);
    }

    // Update existing Kelas
    public function update(Request $request, $id)
    {
        $kelas = Kelas::find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'sekolah_id' => 'sometimes|required|exists:sekolah,id',
            'jurusan' => 'sometimes|required|string',
            'kelas' => 'sometimes|required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        $kelas->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil diupdate',
            'data' => $kelas
        ]);
    }

    // Delete a Kelas
    public function destroy($id)
    {
        $kelas = Kelas::find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan'
            ], 404);
        }

        $kelas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil dihapus'
        ]);
    }


    // get sortir per kelas
    public function filterKelas(Request $request)
{
    $jurusan = $request->get('jurusan');

    $kelas = Kelas::where('jurusan', 'like', '%' . $jurusan . '%')
        ->with('siswa') // Mengambil data siswa yang terkait
        ->get();

    if ($kelas->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Kelas tidak ditemukan',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data kelas berhasil disaring',
        'data' => $kelas
    ]);
}

// sortir get sekolah
public function filterBySekolah(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'sekolah_id' => 'required|exists:sekolah,id',
    ]);

    // Fetch classes/jurusan associated with the given sekolah_id
    $kelas = Kelas::where('sekolah_id', $request->sekolah_id)->get();

    // Check if classes were found
    if ($kelas->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No classes found for the specified school.',
        ], 404);
    }

    // Return the classes with their associated data
    return response()->json([
        'success' => true,
        'data' => $kelas,
    ]);
}

}


