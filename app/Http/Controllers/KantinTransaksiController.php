<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinTransaksiRequest;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class KantinTransaksiController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $items = KantinTransaksi::latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }

    public function create(KantinTransaksiRequest $request)
    {
        $fields = $request->validated();
        $produk = KantinProduk::find($fields['kantin_produk_id']);
        $fields['harga'] = $produk->harga;
        $fields['harga_total'] = $produk->harga * $fields['jumlah'];
        try {
            $item = KantinTransaksi::create($fields);
            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
