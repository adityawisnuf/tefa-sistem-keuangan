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
        $transaksi = KantinTransaksi::paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function create(KantinTransaksiRequest $request)
    {
        $fields = $request->validated();

        $produk = KantinProduk::find($fields['kantin_produk_id']);
        $fields['harga'] = $produk->harga;
        $fields['harga_total'] = $fields['harga'] * $fields['jumlah'];
        try {
            $transaksi = KantinTransaksi::create($fields);
            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(KantinTransaksi $transaksi)
    {
        if (in_array($transaksi['status'], ['dibatalkan', 'selesai'])) {
            return response()->json(['message' => 'Pesanan sudah selesai!'], Response::HTTP_UNAUTHORIZED);
        }

        switch ($transaksi['status']) {
            case 'proses':
                $transaksi->update(['status' => 'siap_diambil']);
                return response()->json(['data' => $transaksi], Response::HTTP_OK);

            case 'siap_diambil':
                $transaksi->update(['status' => 'selesai']);
                return response()->json(['data' => $transaksi], Response::HTTP_OK);
        }
    }

    public function confirmInitialTransaction(KantinTransaksiRequest $request, KantinTransaksi $transaksi)
    {
        $fields = $request->validated();

        $transaksi->update($fields);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
}
