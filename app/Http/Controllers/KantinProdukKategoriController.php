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

        $items = KantinProdukKategori::latest()->paginate($perPage);

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


    public function show($id)
    {
        $kategori = KantinProdukKategori::findOrFail($id);

        return response()->json(['data' => $kategori], Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:255'],
        ]);

        $kategori = KantinProdukKategori::findOrFail($id);
        $kategori->update($validated);

        return response()->json(['data' => $kategori], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $kategori = KantinProdukKategori::findOrFail($id);
        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.'], Response::HTTP_OK);
    }
}