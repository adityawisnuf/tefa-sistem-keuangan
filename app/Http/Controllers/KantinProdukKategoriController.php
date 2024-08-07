<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinProdukKategoriRequest;
use App\Models\KantinProdukKategori;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KantinProdukKategoriController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $items = KantinProdukKategori::latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }


    public function create(KantinProdukKategoriRequest $request)
    {
        $fields = $request->validated();

        try {
            $item = KantinProdukKategori::create($fields);
            return response()->json(['data' => $item], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan kategori: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(KantinProdukKategoriRequest $request, KantinProdukKategori $kategori)
    {
        $fields = $request->validated();

        try {
            $kategori->update($fields);
            return response()->json(['data' => $kategori], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui kategori: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(KantinProdukKategori $kategori)
    {
        try {
            $kategori->delete();
            return response(null, 204);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus kategori: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
