<?php

namespace App\Http\Controllers\Api;

use App\Models\AsetSekolah;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AsetResource;
use Illuminate\Support\Facades\Validator;

class AsetController extends Controller
{
   public function index()
   {
       $aset = AsetSekolah::latest()->paginate(5);
       return new AsetResource(true, 'List Inventaris', $aset);
   }

   public function store(Request $request)
   {
       $validator = Validator::make($request->all(), [
           'tipe' => 'required|string|max:255',
           'nama' => 'required|string|max:255',
           'harga' => 'required|numeric',
           'kondisi' => 'required|string|max:225',
           'penggunaan' => 'required|string',
       ]);

       if ($validator->fails()) {
           return response()->json($validator->errors(), 422);
       }

       $aset = AsetSekolah::create($validator->validated());

       return new AsetResource(true, 'Aset Baru Berhasil Ditambahkan!', $aset);
   }

   public function show($id)
   {
       $aset = AsetSekolah::find($id);

       if (!$aset) {
           return response()->json(['message' => 'Aset tidak ditemukan.'], 404);
       }

       return new AsetResource(true, 'Detail Data Aset!', $aset);
   }

   public function update(Request $request, $id)
   {
       $validator = Validator::make($request->all(), [
           'tipe' => 'required|string|max:255',
           'nama' => 'required|string|max:255',
           'harga' => 'required|numeric',
           'kondisi' => 'required|string|max:225',
           'penggunaan' => 'required|string', // Perbaiki dari 'text' ke 'string'
       ]);

       if ($validator->fails()) {
           return response()->json($validator->errors(), 422);
       }

       $aset = AsetSekolah::find($id);

       if (!$aset) {
           return response()->json(['message' => 'Aset tidak ditemukan.'], 404);
       }

       $aset->update($validator->validated());

       return new AsetResource(true, 'Aset Berhasil Diubah!', $aset);
   }

   public function destroy($id)
   {
       $aset = AsetSekolah::find($id);

       if (!$aset) {
           return response()->json(['message' => 'Aset tidak ditemukan.'], 404);
       }

       $aset->delete();

       return new AsetResource(true, 'Data Aset Berhasil Dihapus!', null);
   }
}
