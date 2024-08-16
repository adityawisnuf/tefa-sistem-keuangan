<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiswaKantinRequest;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\SiswaWalletRiwayat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SiswaKantinController extends Controller
{
    public function getProduk()
    {
        $kategori = request()->input('kategori', 1);
        $perPage = request()->input('per_page', 10);
        $produk = KantinProduk::where('kantin_produk_kategori_id', $kategori)->latest()->paginate($perPage);
        return response()->json(['data' => $produk], Response::HTTP_OK);
    }

    public function getProdukDetail(KantinProduk $produk)
    {
        return response()->json(['data' => $produk], Response::HTTP_OK);
    }

    public function createProdukTransaksi(SiswaKantinRequest $request, KantinProduk $produk)
    {
        $siswa = Auth::user()->siswa->first();
        $fields = $request->validated();

        $siswaWallet = $siswa->siswa_wallet;

        $fields['siswa_id'] = $siswa->id;
        $fields['kantin_id'] = $produk->kantin_id;
        $fields['kantin_produk_id'] = $produk->id;
        $fields['harga'] = $produk->harga;
        $fields['harga_total'] = $fields['harga'] * $fields['jumlah'];

        try {
            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk transaksi ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($fields['jumlah'] > $produk->stok) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi untuk jumlah yang dipesan.',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();
            $transaksi = KantinTransaksi::create($fields);
            $kantin = $transaksi->kantin;

            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal - $fields['harga_total']
            ]);

            $kantin->update([
                'saldo' => $kantin->saldo + $fields['harga_total']
            ]);

            SiswaWalletRiwayat::create([
                'siswa_wallet_id' => $siswaWallet->id,
                'merchant_order_id' => null,
                'tipe_transaksi' => 'pengeluaran',
                'nominal' => $fields['harga_total']
            ]);
            DB::commit();

            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getKantinRiwayat()
    {
        $siswa = Auth::user()->siswa->first();
        $perPage = request()->input('per_page', 10);
        $riwayat = $siswa->kantin_transaksi()->paginate($perPage);
        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
}

