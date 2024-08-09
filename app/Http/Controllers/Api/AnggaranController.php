<?php

namespace App\Http\Controllers\Api;

use App\Models\Anggaran;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnggaranResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnggaranController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        $anggaran = Anggaran::latest()->get();
        return new AnggaranResource(true, 'List Anggaran', $anggaran);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        // Cast string "true"/"false" to boolean
        $request->merge(['status' => filter_var($request->status, FILTER_VALIDATE_BOOLEAN)]);

        $validator = Validator::make($request->all(), [
            'nama_anggaran' => 'required|string|max:225',
            'nominal' => 'required|numeric',
            'deskripsi' => 'required|string',
            'tanggal_pengajuan' => 'required|date',
            'target_terealisasikan' => 'required|date',
            'status' => 'required|boolean',
            'pengapprove' => 'required|string|max:225',
            'pengapprove_jabatan' => 'required|string|max:225',
            'catatan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $anggaran = Anggaran::create($validator->validated());

        return new AnggaranResource(true, 'Anggaran Berhasil Ditambahkan!', $anggaran);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  int $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        // Cast string "true"/"false" to boolean
        $request->merge(['status' => filter_var($request->status, FILTER_VALIDATE_BOOLEAN)]);

        $validator = Validator::make($request->all(), [
            'nama_anggaran' => 'required|string|max:225',
            'nominal' => 'required|numeric',
            'deskripsi' => 'required|string',
            'tanggal_pengajuan' => 'required|date',
            'target_terealisasikan' => 'required|date',
            'status' => 'required|boolean',
            'pengapprove' => 'required|string|max:225',
            'pengapprove_jabatan' => 'required|string|max:225',
            'catatan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $anggaran = Anggaran::find($id);
        if (!$anggaran) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        $anggaran->update($validator->validated());

        return new AnggaranResource(true, 'Anggaran Berhasil Diubah!', $anggaran);
    }

    /**
     * destroy
     *
     * @param  int $id
     * @return void
     */
    public function destroy($id)
    {
        $anggaran = Anggaran::find($id);
        if (!$anggaran) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        $anggaran->delete();

        return new AnggaranResource(true, 'Data Anggaran Berhasil Dihapus!', null);
    }
}
