<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PendaftarAkademik;
use Illuminate\Support\Facades\Validator;

class PendaftarAkademikController extends Controller
{
    public function index()
    {
        $pendaftarAkademik = PendaftarAkademik::all();
        return response()->json($pendaftarAkademik);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ppdb_id' => 'required|integer|exists:ppdbs,id',
            'sekolah_asal' => 'required|string|max:255',
            'tahun_lulus' => 'required|date_format:Y',
            'jurusan_tujuan' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $pendaftarAkademik = PendaftarAkademik::create($request->all());
        return response()->json($pendaftarAkademik, 201);
    }

    public function show($id)
    {
        $pendaftarAkademik = PendaftarAkademik::find($id);
        if (!$pendaftarAkademik) {
            return response()->json(['message' => 'Not Found!'], 404);
        }
        return response()->json($pendaftarAkademik);
    }

    public function update(Request $request, $id)
    {
        $pendaftarAkademik = PendaftarAkademik::find($id);
        if (!$pendaftarAkademik) {
            return response()->json(['message' => 'Not Found!'], 404);
        }

        $validator = Validator::make($request->all(), [
            'ppdb_id' => 'integer|exists:ppdbs,id',
            'sekolah_asal' => 'string|max:255',
            'tahun_lulus' => 'date_format:Y',
            'jurusan_tujuan' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $pendaftarAkademik->update($request->all());
        return response()->json($pendaftarAkademik);
    }

    public function destroy($id)
    {
        $pendaftarAkademik = PendaftarAkademik::find($id);
        if (!$pendaftarAkademik) {
            return response()->json(['message' => 'Not Found!'], 404);
        }
        $pendaftarAkademik->delete();
        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}
