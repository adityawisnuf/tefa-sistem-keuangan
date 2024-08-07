<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinProdukRequest;
use App\Models\KantinProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KantinProdukController extends Controller
{
    const IMAGE_STORAGE_PATH = 'public/kantin/produk/';
    public function index()
    {
        $items = KantinProduk::latest()->paginate(3);
        return response()->json([
            'data' => $items,
            'message' => 'List item.'
        ], 200);
    }

    public function create(KantinProdukRequest $request)
    {
        $fields = $request->validated();

        $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_produk']);
        $fields['foto_produk'] = basename($path);
        $item = KantinProduk::create($fields);

        return response()->json([
            'data' => $item,
            'message' => 'Item created.'
        ], 201);
    }


    public function update(KantinProdukRequest $request, KantinProduk $produk)
    {
        $fields = array_filter($request->validated());

        if (isset($fields['foto_produk'])) {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_produk']);
            Storage::delete(self::IMAGE_STORAGE_PATH . $produk->foto_produk);
            $fields['foto_produk'] = basename($path);
        }

        $produk->update($fields);

        return response()->json([
            'data' => $produk,
            'message' => 'Item updated.'
        ], 200);
    }

    public function destroy(KantinProduk $produk)
    {
        Storage::delete(self::IMAGE_STORAGE_PATH . $produk->foto_produk);
        $produk->delete();

        return response(null, 204);
    }
}
