<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinProdukRequest;
use App\Models\KantinProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class KantinProdukController extends Controller
{
    const IMAGE_STORAGE_PATH = 'public/kantin/produk/';

    public function index(Request $request)
    {
        $validated = $request->validate([
            'usaha' => ['nullable', 'integer', 'min:1'],
            'nama_kategori' => ['nullable', 'in:makanan,minuman'],
            'nama_produk' => ['nullable', 'string'],
            'status' => ['nullable', 'in:aktif,tidak_aktif'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $usaha = Auth::user()->usaha;
        $namaKategori = $validated['nama_kategori'] ?? null;
        $namaProduk = $validated['nama_produk'] ?? null;
        $status = $validated['status'] ?? null;
        $perPage = $validated['per_page'] ?? 10;

        $produk = $usaha
            ->kantin_produk()
            ->select('id', 'nama_produk', 'foto_produk', 'deskripsi', 'harga_jual', 'stok')
            ->when($status, function ($query) use ($status) {
                $query->where('status', 'like', "%$status%");
            })
            ->when($namaProduk, function ($query) use ($namaProduk) {
                $query->where('nama_produk', 'like', "%$namaProduk%");
            })
            ->when($namaKategori, function ($query) use ($namaKategori) {
                $query->whereRelation('kantin_produk_kategori', 'nama_kategori', 'like', "%$namaKategori%");
            })
            ->paginate($perPage);

        return response()->json(['data' => $produk], Response::HTTP_OK);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'kantin_produk_kategori_id' => ['required', 'exists:kantin_produk_kategori,id'],
            'nama_produk' => ['required', 'string', 'max:255'],
            'foto_produk' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['required', 'string'],
            'harga_pokok' => ['required', 'integer', 'min:0'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'stok' => ['sometimes', 'integer', 'min:0'],
        ]);

        $usaha = Auth::user()->usaha;
        $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $validated['foto_produk']);
        $validated['foto_produk'] = basename($path);
        $validated['usaha_id'] = $usaha->id;
        $data = KantinProduk::create($validated);

        return response()->json(['data' => $data], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $produk = KantinProduk
            ::select('id', 'nama_produk', 'foto_produk', 'deskripsi', 'harga_jual', 'stok')
            ->findOrFail($id);

        return response()->json(['data' => $produk], Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kantin_produk_kategori_id' => ['sometimes', 'required', 'exists:kantin_produk_kategori,id'],
            'nama_produk' => ['sometimes', 'required', 'string', 'max:255'],
            'foto_produk' => ['sometimes', 'required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['sometimes', 'required', 'string'],
            'harga_pokok' => ['sometimes', 'required', 'integer', 'min:0'],
            'harga_jual' => ['sometimes', 'required', 'integer', 'min:0'],
            'stok' => ['sometimes', 'required', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'in:aktif,tidak_aktif'],
        ]);

        $produk = KantinProduk::findOrFail($id);

        if (isset($validated['foto_produk'])) {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $validated['foto_produk']);
            Storage::delete(self::IMAGE_STORAGE_PATH . $produk->foto_produk);
            $validated['foto_produk'] = basename($path);
        }

        $produk->update($validated);

        return response()->json(['data' => $produk], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $produk = KantinProduk::findOrFail($id);

        $produk->delete();
        Storage::delete(self::IMAGE_STORAGE_PATH . $produk->foto_produk);

        return response()->json(['message' => 'Data berhasil dihapus.'], Response::HTTP_OK);
    }
}
