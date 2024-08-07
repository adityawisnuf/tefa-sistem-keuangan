<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinProdukKategoriRequest;
use App\Models\KantinProdukKategori;
use Illuminate\Http\Request;

class KantinProdukKategoriController extends Controller
{
    public function index()
    {
        $items = KantinProdukKategori::latest()->paginate(3);
        return response()->json([
            'data' => $items,
            'message' => 'List item.'
        ], 200);
    }


    public function create(KantinProdukKategoriRequest $request)
    {
        $fields = $request->validated();

        $item = KantinProdukKategori::create($fields);

        return response()->json([
            'data' => $item,
            'message' => 'Item created.'
        ], 201);
    }

    public function update(KantinProdukKategoriRequest $request, KantinProdukKategori $kategori)
    {
        $fields = array_filter($request->validated());



        $kategori->update;

        return response()->json([
            'data' => $kategori,
            'message' => 'Item updated.'
        ], 200);
    }

    public function destroy(KantinProdukKategori $item)
    {

        $item->delete();

        return response(null, 204);
    }
}
