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
        $search = $request->query('search', '');
        $query = AsetSekolah::oldest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('kondisi', 'like', "%$search%")
                    ->orWhere('penggunaan', 'like', "%$search%");
            });
        }

        $asset = $request->input('page') === 'all' ? $query->get() : $query->paginate(5);
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
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'tipe' => 'integer|in:1,2',
            'kondisi' => 'integer|in:1,2,3',
            'harga' => 'required|numeric|min:0',
            'penggunaan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $asset = AsetSekolah::create($validator->validated());
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
            'nama' => 'required|string|max:255',
            'tipe' => 'integer|in:1,2',
            'kondisi' => 'integer|in:1,2,3',
            'harga' => 'required|numeric|min:0',
            'penggunaan' => 'required|string',
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