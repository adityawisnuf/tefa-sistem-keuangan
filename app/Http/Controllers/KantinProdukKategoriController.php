<?php

namespace App\Http\Controllers;

use App\Models\KantinProdukKategori;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KantinProdukKategoriController extends Controller
{

    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $items = KantinProdukKategori::paginate($perPage);

        return response()->json(['data' => $items], Response::HTTP_OK);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:255'],
        ]);

        $item = KantinProdukKategori::create($validated);

        return response()->json(['data' => $item], Response::HTTP_CREATED);
    }


    public function show(KantinProdukKategori $kategori)
    {
        return response()->json(['data' => $kategori], Response::HTTP_OK);
    }

    public function update(Request $request, KantinProdukKategori $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => ['sometimes', 'nullable', 'string', 'max:255'],
            'deskripsi' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $fields = array_filter($validated);

        $kategori->update($fields);

        return response()->json(['data' => $kategori], Response::HTTP_OK);
    }

    public function destroy(KantinProdukKategori $kategori)
    {
        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.'], Response::HTTP_OK);
    }
}