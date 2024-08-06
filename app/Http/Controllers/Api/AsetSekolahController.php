<?php

namespace App\Http\Controllers\Api;

//import Model "Asset"
use App\Models\AsetSekolah;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AsetSekolahResource;
//import Resource "AssetResource"
use App\Http\Resources\AsetSekolahResourceResource;
use App\Http\Resources\AssetResource;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class AsetSekolahController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        $asset = AsetSekolah::latest()->get();
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
            'kategori' => 'required|string|max:255',
            'tanggal_pembelian' => 'required|date',
            'harga' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'jumlah' => 'required|integer',
            'kondisi_baik' => 'required|integer',
            'kondisi_kurang_baik' => 'required|integer',
            'kondisi_buruk' => 'required|integer',
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
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'tanggal_pembelian' => 'required|date',
            'harga' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'jumlah' => 'required|integer',
            'kondisi_baik' => 'required|integer',
            'kondisi_kurang_baik' => 'required|integer',
            'kondisi_buruk' => 'required|integer',
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
