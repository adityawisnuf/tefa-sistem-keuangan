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

        $item = KantinProdukKategori::create($fields);
        return response()->json(['data' => $item], Response::HTTP_CREATED);
    }

    public function show(KantinProdukKategori $kategori)
    {
        return response()->json(['data' => $kategori], Response::HTTP_OK);
    }

    public function update(KantinProdukKategoriRequest $request, KantinProdukKategori $kategori)
    {
        $fields = $request->validated();
        
        $kategori->update($fields);
        return response()->json(['data' => $kategori], Response::HTTP_OK);
    }

    public function destroy(KantinProdukKategori $kategori)
    {
        $kategori->delete();
        return response(null, Response::HTTP_NO_CONTENT);

    }
}
