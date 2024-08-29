<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiswaKantinRequest;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\KantinTransaksiDetail;
use App\Models\SiswaWalletRiwayat;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;
use Symfony\Component\HttpFoundation\Response;

class SiswaKantinController extends Controller
{
    public function getProduk()
    {
        $perPage = request('per_page', 10);
        $nama_produk = request('nama_produk');
        $nama_kategori = request('nama_kategori');

        $produk = KantinProduk::where('status', 'aktif')
            ->when($nama_produk, function ($query) use ($nama_produk) {
                $query->where('nama_produk', 'like', "%$nama_produk%");
            })
            ->when($nama_kategori, function ($query) use ($nama_kategori) {
                $query->whereRelation('kantin_produk_kategori', 'nama_kategori', 'like', "%$nama_kategori%");
            })
            ->paginate($perPage);

        return response()->json(['data' => $produk], Response::HTTP_OK);
    }


    public function getProdukDetail(KantinProduk $produk)
    {
        if ($produk->status == 'aktif') return response()->json(['data' => $produk], Response::HTTP_OK);
        return response()->json(['data' => 'produk tidak tersedia'], Response::HTTP_BAD_REQUEST);
    }

    public function createProdukTransaksi(SiswaKantinRequest $request)
    {
        $siswa = Auth::user()->siswa->firstOrFail();
        $fields = $request->validated();

        $productIds = collect($fields['detail_pesanan'])->pluck('kantin_produk_id')->toArray();
        $products = KantinProduk::whereIn('id', $productIds)->get();

        $usaha = $products->first()->usaha;
        $siswaWallet = $siswa->siswa_wallet;

        $fields['siswa_id'] = $siswa->id;
        $fields['usaha_id'] = $usaha->id;

        DB::beginTransaction();
        $kantinTransaksi = KantinTransaksi::create($fields);
        $totalHarga = 0;

        foreach ($fields['detail_pesanan'] as $productDetail) {
            $product = $products->firstWhere('id', $productDetail['kantin_produk_id']);
            $qty = $productDetail['jumlah'];

            if ($product->stok < $qty) {
                return response()->json(['message' => "Stok produk {$product->nama_produk} tidak mencukupi."], Response::HTTP_BAD_REQUEST);
            }

            $product->stok -= $qty;

            KantinTransaksiDetail::create([
                'kantin_produk_id' => $product->id,
                'kantin_transaksi_id' => $kantinTransaksi->id,
                'jumlah' => $qty,
                'harga' => $product->harga_jual,
            ]);

            $totalHarga += $product->harga_jual * $qty;
        }

        if ($siswaWallet->nominal < $totalHarga) {
            return response()->json(['message' => 'Saldo tidak mencukupi untuk transaksi ini.'], Response::HTTP_BAD_REQUEST);
        }

        $usaha->update([
            'saldo' => $usaha->saldo + $totalHarga,
        ]);

        $siswaWallet->update([
            'nominal' => $siswaWallet->nominal - $totalHarga,
        ]);

        $products->each->save();

        SiswaWalletRiwayat::create([
            'siswa_wallet_id' => $siswaWallet->id,
            'merchant_order_id' => null,
            'tipe_transaksi' => 'pengeluaran',
            'nominal' => $totalHarga,
        ]);
        DB::commit();

        return response()->json(['data' => $kantinTransaksi], Response::HTTP_CREATED);
    }



    public function getKantinRiwayat()
    {
        $siswa = Auth::user()->siswa()->first();
        $perPage = request('per_page', 10);

        try {
            $riwayat = $siswa->kantin_transaksi()
            ->with(['usaha', 'kantin_transaksi_detail.kantin_produk'])
            ->whereIn('status',['dibatalkan','selesai'])
            ->paginate($perPage);

            $riwayat->getCollection()->transform(function ($riwayat) {
                return [
                    'id' => $riwayat->id,
                    'nama_usaha' => $riwayat->usaha->nama_usaha,
                    'jumlah_layanan' => count($riwayat->kantin_transaksi_detail),
                    'harga_total' => array_reduce($riwayat->kantin_transaksi_detail->toArray(), function($scary, $item) {
                        return $scary += $item['harga_total']; //horror sikit
                    }),
                    'status' => $riwayat->status,
                    'tanggal_pemesanan' => $riwayat->tanggal_pemesanan,
                    'tanggal_selesai' => $riwayat->tanggal_selesai,
                ];
            });

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getLayananRiwayat: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data riwayat transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getKantinTransaksi()
    {
        $siswa = Auth::user()->siswa()->first();
        $perPage = request('per_page', 10);

        try {
            $riwayat = $siswa->kantin_transaksi()
            ->with(['usaha', 'kantin_transaksi_detail.kantin_produk'])
            ->whereIn('status',['pending','siap_diambil','proses'])
            ->paginate($perPage);

            $riwayat->getCollection()->transform(function ($riwayat) {
                return [
                    'id' => $riwayat->id,
                    'nama_usaha' => $riwayat->usaha->nama_usaha,
                    'jumlah_layanan' => count($riwayat->kantin_transaksi_detail),
                    'harga_total' => array_reduce($riwayat->kantin_transaksi_detail->toArray(), function($scary, $item) {
                        return $scary += $item['harga_total']; //horror sikit
                    }),
                    'status' => $riwayat->status,
                    'tanggal_pemesanan' => $riwayat->tanggal_pemesanan,
                    'tanggal_selesai' => $riwayat->tanggal_selesai,
                ];
            });

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getLayananRiwayat: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data riwayat transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
