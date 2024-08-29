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
        $usaha = Auth::user()->usaha->firstOrFail();
        $perPage = request('per_page', 10);
        $namaKategori = request('nama_kategori');
        $namaProduk = request('nama_produk');
        $status = request('status');

        try {
            $produk = $usaha->kantin_produk()
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
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    public function create(KantinProdukRequest $request)
    {
        $usaha = Auth::user()->usaha->firstOrFail();
        $fields = $request->validated();
        
        try {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_produk']);
            $fields['foto_produk'] = basename($path);
            $fields['usaha_id'] = $usaha->id;
            $item = KantinProduk::create($fields);
            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat membuat data produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(KantinProduk $produk)
    {
        return response()->json(['data' => $produk], Response::HTTP_OK);
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
            return response()->json(['error' => 'Terjadi kesalahan saat mengubah data produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function destroy(KantinProduk $produk)
    {
        try {
            Storage::delete(self::IMAGE_STORAGE_PATH . $produk->foto_produk);
            $produk->delete();
            return response(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus data produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
