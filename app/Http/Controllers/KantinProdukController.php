<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinProdukRequest;
use App\Models\KantinProduk;
use Exception;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class KantinProdukController extends Controller
{
    const IMAGE_STORAGE_PATH = 'public/kantin/produk/';

    public function index()
    {
        $kantin = Auth::user()->usaha()->first();

        $perPage = request()->input('per_page', 10);
        $items = $kantin->kantin_produk()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }

    public function create(KantinProdukRequest $request)
    {
        $kantin = Auth::user()->usaha()->first();
        $fields = $request->validated();

        try {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_produk']);
            $fields['foto_produk'] = basename($path);
            $fields['kantin_id'] = $kantin->id;
            $item = KantinProduk::create($fields);
            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan produk: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function update(KantinProdukRequest $request, KantinProduk $produk)
    {
        $fields = array_filter($request->validated());

        try {
            if (isset($fields['foto_produk'])) {
                $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_produk']);
                Storage::delete(self::IMAGE_STORAGE_PATH . $produk->foto_produk);
                $fields['foto_produk'] = basename($path);
            }
            $produk->update($fields);
            return response()->json(['data' => $produk], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui produk: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(KantinProduk $produk)
    {
        try {
            Storage::delete(self::IMAGE_STORAGE_PATH . $produk->foto_produk);
            $produk->delete();
            return response(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus produk: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
