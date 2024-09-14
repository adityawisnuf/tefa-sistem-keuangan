<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinProdukRequest;
use App\Models\KantinProduk;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class KantinProdukController extends Controller
{
    const IMAGE_STORAGE_PATH = 'public/kantin/produk/';

    public function index()
    {
        $validator = Validator::make(request()->all(), [
            'usaha' => ['nullable', 'integer', 'min:1'],
            'nama_kategori' => ['nullable', 'in:makanan,minuman'],
            'nama_produk' => ['nullable', 'string'],
            'status' => ['nullable', 'in:aktif,tidak_aktif'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $usaha = Auth::user()->usaha->firstOrFail();
        $namaKategori = request('nama_kategori');
        $namaProduk = request('nama_produk');
        $status = request('status');
        $perPage = request('per_page', 10);

        try {
            $produk = $usaha
                ->kantin_produk()
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
            Log::error('index: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(KantinProdukRequest $request)
    {
        $validator = Validator::make(request()->all(), [
            'usaha' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $usaha = Auth::user()->usaha->firstOrFail();
        $fields = $request->validated();

        try {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_produk']);
            $fields['foto_produk'] = basename($path);
            $fields['usaha_id'] = $usaha->id;
            $item = KantinProduk::create($fields);
            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('create: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat membuat data produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $produk = KantinProduk::findOrFail($id);
            return response()->json(['data' => $produk], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('show: ' . $e->getMessage());
            return response()->json(['error' => 'Produk tidak ditemukan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(KantinProdukRequest $request, $id)
    {
        $fields = $request->validated();    
        $produk = KantinProduk::findOrFail($id);
        $this->authorize('update', $produk);

        if (isset($fields['foto_produk'])) {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_produk']);
            Storage::delete(self::IMAGE_STORAGE_PATH . $produk->foto_produk);
            $fields['foto_produk'] = basename($path);
        }

        $produk->update($fields);

        return response()->json(['data' => $produk], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        try {
            $produk = KantinProduk::findOrFail($id);

            // Cek izin dengan policy
            $this->authorize('delete', $produk);

            // Hapus foto produk jika ada
            Storage::delete(self::IMAGE_STORAGE_PATH . $produk->foto_produk);

            // Hapus produk dari database
            $produk->delete();

            return response(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            // Tangani error lainnya
            Log::error('destroy: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus data produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
