<?php
namespace App\Http\Controllers\Api;
use App\Models\Anggaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnggaranResource;
use Illuminate\Support\Facades\Validator;


class AnggaranController extends Controller
{

    public function index(Request $request)
    {
        // Ambil parameter pencarian dari query string, jika ada
        $search = $request->query('search', '');

        // Mulai dengan query untuk mengambil data terbaru
        $query = Anggaran::latest();

        // Tambahkan kondisi pencarian jika ada parameter 'search'
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Anda dapat menyesuaikan kolom yang dicari sesuai kebutuhan
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

        // Lakukan paginasi pada hasil query
        $anggaran = $query->paginate(5);
   
        // Kembalikan hasil dalam format resource
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
            'status' => 'required|integer|in:1,2,3,4',
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
