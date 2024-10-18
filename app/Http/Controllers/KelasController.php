<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::all();
        return response()->json($kelas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jurusan' => 'required|string|max:255',
            'kelas' => 'required|string|max:255',
        ]);

        $kelas = Kelas::create([
            'sekolah_id' => 1,
            'jurusan' => $request->input('jurusan'),
            'kelas' => $request->input('kelas'),
        ]);

        return response()->json($kelas, 201);
    }

    public function show($id)
    {
        $kelas = Kelas::findOrFail($id);
        return response()->json($kelas);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sekolah_id' => 'sometimes|required|exists:sekolah,id',
            'jurusan' => 'sometimes|required|string|max:255',
            'kelas' => 'sometimes|required|string|max:255',
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->update($request->all());

        return response()->json($kelas);
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return response()->json(null, 204);
    }
}
