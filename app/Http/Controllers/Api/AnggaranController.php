<?php
namespace App\Http\Controllers\Api;
use App\Models\AnggaranSekolah;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnggaranResource;
use Illuminate\Support\Facades\Validator;


class AnggaranController extends Controller
{
   public function index()
   {
       $anggaran = Anggaran::latest()->paginate(5);
       return new AnggaranResource(true, 'List Anggaran', $anggaran);
   }
   public function store(Request $request)
   {
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
       //check if validation fails
       if ($validator->fails()) {
           return response()->json($validator->errors(), 422);
       }




       $anggaran = Anggaran::create($validator->validated());




       //return response
       return new AnggaranResource(true, 'Anggaran Baru Berhasil Ditambahkan!', $anggaran);
   }


   public function show($id)
   {
       $anggaran = Anggaran::find($id);
       return new AnggaranResource(true, 'Detail Data Anggaran!', $anggaran);
   }


   public function update(Request $request, $id)
   {
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
       $anggaran->update($validator->validated());
       return new AnggaranResource(true, 'Anggaran Berhasil Diubah!', $anggaran);
   }
   public function destroy($id)
   {
       $anggaran = Anggaran::find($id);
       $anggaran->delete();
       return new AnggaranResource(true, 'Data Anggaran Berhasil Dihapus!', null);
   }
}
