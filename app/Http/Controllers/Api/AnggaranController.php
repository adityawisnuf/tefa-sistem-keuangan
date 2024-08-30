<?php

namespace App\Http\Controllers\Api;

use App\Models\Anggaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnggaranResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class AnggaranController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $query = Anggaran::oldest(); // Mengurutkan berdasarkan tanggal terlama

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_anggaran', 'like', "%$search%")
                    ->orWhere('nominal', 'like', "%$search%")
                    ->orWhere('deskripsi', 'like', "%$search%")
                    ->orWhere('tanggal_pengajuan', 'like', "%$search%")
                    ->orWhere('target_terealisasikan', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('pengapprove', 'like', "%$search%")
                    ->orWhere('pengapprove_jabatan', 'like', "%$search%")
                    ->orWhere('catatan', 'like', "%$search%");
            });
        }

        $anggaran = $request->input('page') === 'all' ? $query->get() : $query->paginate(5); // Menggunakan pagination

        return new AnggaranResource(true, 'List Anggaran', $anggaran);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_anggaran' => 'string|max:225',
            'nominal' => 'numeric',
            'deskripsi' => 'string',
            'tanggal_pengajuan' => 'date',
            'target_terealisasikan' => 'date|nullable',
            'status' => 'integer|in:1,2,3,4',
            'pengapprove' => 'nullable|string|max:225',
            'pengapprove_jabatan' => 'nullable|string|max:225',
            'nominal_diapprove' => 'numeric|nullable',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Additional validation if status is 'Diapprove'
        if ($request->input('status') == 2) {
            $validator->sometimes('pengapprove', 'required|string|max:225', function ($input) {
                return !empty($input->pengapprove);
            });
            $validator->sometimes('pengapprove_jabatan', 'required|string|max:225', function ($input) {
                return !empty($input->pengapprove_jabatan);
            });
        }

        $anggaran = Anggaran::create($validator->validated());

        return new AnggaranResource(true, 'Anggaran Baru Berhasil Ditambahkan!', $anggaran);
    }

    public function show($id)
    {
        $anggaran = Anggaran::find($id);
        if (!$anggaran) {
            return response()->json(['success' => false, 'message' => 'Anggaran tidak ditemukan'], 404);
        }
        return new AnggaranResource(true, 'Detail Data Anggaran!', $anggaran);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_anggaran' => 'string|max:225',
            'nominal' => 'numeric',
            'deskripsi' => 'string',
            'tanggal_pengajuan' => 'date',
            'target_terealisasikan' => 'date',
            'status' => 'integer|in:1,2,3,4',
            'pengapprove' => 'nullable|string|max:225',
            'pengapprove_jabatan' => 'nullable|string|max:225',
            'nominal_diapprove' => 'numeric',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->input('status') == 2) {
            $validator->sometimes('pengapprove', 'required|string|max:225', function ($input) {
                return !empty($input->pengapprove);
            });
            $validator->sometimes('pengapprove_jabatan', 'required|string|max:225', function ($input) {
                return !empty($input->pengapprove_jabatan);
            });
        }

        $anggaran = Anggaran::find($id);
        if (!$anggaran) {
            return response()->json(['success' => false, 'message' => 'Anggaran tidak ditemukan'], 404);
        }

        $anggaran->update($validator->validated());

        return new AnggaranResource(true, 'Anggaran Berhasil Diubah!', $anggaran);
    }

    public function destroy($id)
    {
        $anggaran = Anggaran::find($id);
        if (!$anggaran) {
            return response()->json(['success' => false, 'message' => 'Anggaran tidak ditemukan'], 404);
        }
        $anggaran->delete();
        return new AnggaranResource(true, 'Data Anggaran Berhasil Dihapus!', null);
    }
}