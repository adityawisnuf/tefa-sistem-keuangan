<?php

namespace App\Http\Controllers\Api;

//import Model "Asset"
use App\Models\AsetSekolah;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use Illuminate\Support\Facades\Validator;

class AsetSekolahController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index(Request $request)
    {
        // Ambil parameter pencarian dari query string, jika ada
        $search = $request->query('search', '');

        // Mulai dengan query untuk mengambil data terbaru
        $query = AsetSekolah::latest();

        // Tambahkan kondisi pencarian jika ada parameter 'search'
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Anda dapat menyesuaikan kolom yang dicari sesuai kebutuhan
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('kondisi', 'like', "%$search%")
                    ->orWhere('penggunaan', 'like', "%$search%");
            });
        }

        // Lakukan paginasi pada hasil query
        $asset = $query->paginate(5);

        // Kembalikan hasil dalam format resource
        return new AssetResource(true, 'List Inventaris', $asset);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'tipe' => 'required|string|max:225',
            'kondisi' => 'required|string',
            'harga' => 'required|numeric',
            'penggunaan' => 'required|string'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $asset = AsetSekolah::create($validator->validated());

        //return response
        return new AssetResource(true, 'Asset Baru Berhasil Ditambahkan!', $asset);
    }
    /**
     * show
     *
     * @param  mixed $asset
     * @return void
     */
    public function show($id)
    {
        //find post by ID
        $asset = AsetSekolah::find($id);

        //return single post as a resource
        return new AssetResource(true, 'Detail Data Asset!', $asset);
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tipe' => 'required|string|max:225',
            'nama' => 'required|string|max:255',
            'kondisi' => 'required|string',
            'harga' => 'required|numeric',
            'penggunaan' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $asset = AsetSekolah::find($id);
        $asset->update($validator->validated());
        return new AssetResource(true, 'Asset Berhasil Diubah!', $asset);
    }

    /**
     * destroy
     *
     * @param  mixed $asset
     * @return void
     */
    public function destroy($id)
    {

        $asset = AsetSekolah::find($id);
        $asset->delete();
        return new AssetResource(true, 'Data Asset Berhasil Dihapus!', null);
    }
}
