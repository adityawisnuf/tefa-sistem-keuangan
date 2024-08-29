<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinProdukKategoriRequest;
use App\Models\KantinProdukKategori;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class KantinProdukKategoriController extends Controller
{

    public function index()
{
    $validator = Validator::make(request()->all(), [
        'per_page' => ['nullable', 'integer', 'min:1']
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
    }

    $perPage = request()->input('per_page', 10);

    try {
        $items = KantinProdukKategori::latest()->paginate($perPage);

        return response()->json(['data' => $items], Response::HTTP_OK);
    } catch (Exception $e) {
        Log::error('index: ' . $e->getMessage());
        return response()->json(['error' => 'Terjadi kesalahan saat mengambil data kategori produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    public function create(KantinProdukKategoriRequest $request)
{
    try {
        $fields = $request->validated();
        $item = KantinProdukKategori::create($fields);
        return response()->json(['data' => $item], Response::HTTP_CREATED);
    } catch (Exception $e) {
        Log::error('create: ' . $e->getMessage());
        return response()->json(['error' => 'Terjadi Kesalahan ketika membuat data kategori produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


    public function show($id)
    {
        try {
            $kategori = KantinProdukKategori::findOrFail($id);
            return response()->json(['data' => $kategori], Response::HTTP_OK);
        } catch(Exception $e) {
            Log::error('show: '. $e->getMessage());
            return response()->json(['error' => 'Terjadi Kesalahan ketika menampilkan data Produk Katefori.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(KantinProdukKategoriRequest $request, $id)
    {
        try {
            $fields = $request->validated();
            $kategori = KantinProdukKategori::findOrFail($id);
            $kategori->update($fields);
            return response()->json(['data' => $kategori], Response::HTTP_OK);
        } catch(Exception $e){
            Log::error('update: ' . $e->getMessage() . ' - Kategori ID: ' . $id);
            return response()->json(['error' => 'Terjadi Kesalahan ketika mengupdate data'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } 
    }

    public function destroy($id)
    {
        try {
            $kategori = KantinProdukKategori::findOrFail($id);
            $kategori->delete();
            return response()->json(['message' => 'Data berhasil dihapus'], Response::HTTP_OK);
        } catch(Exception $e) {
            Log::error('destroy: ' . $e->getMessage() . ' - Kategori ID: ' . $id);
            return response()->json(['error' => 'Terjadi Kesalahan ketika menghapus data'], Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }
}