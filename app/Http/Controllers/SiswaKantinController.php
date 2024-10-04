<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\KantinTransaksiDetail;
use App\Models\SiswaWalletRiwayat;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\SocketIoService;

class SiswaKantinController extends Controller
{
    public function getProduk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_produk' => ['nullable', 'string'],
            'nama_kategori' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $perPage = $request->input('per_page', 10);
        $nama_produk = $request->input('nama_produk');
        $nama_kategori = $request->input('nama_kategori');

        try {
            $produk = KantinProduk::where('status', 'aktif')
                ->when($nama_produk, function ($query) use ($nama_produk) {
                    $query->where('nama_produk', 'like', "%$nama_produk%");
                })
                ->when($nama_kategori, function ($query) use ($nama_kategori) {
                    $query->whereRelation('kantin_produk_kategori', 'nama_kategori', 'like', "%$nama_kategori%");
                })
                ->paginate($perPage);

            return response()->json(['data' => $produk], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getProduk: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function getProdukDetail($id)
    {
        $produk = KantinProduk::findOrFail($id);

        try {
            if ($produk->status == 'aktif')
                return response()->json(['data' => $produk], Response::HTTP_OK);
            return response()->json(['data' => 'produk tidak tersedia'], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            Log::error('getProdukDetail: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data produk detail.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createProdukTransaksi(Request $request, SocketIoService $socketIoService)
    {
        $validator = Validator::make($request->all(), [
            'detail_pesanan' => ['required', 'array', 'min:1'],
            'detail_pesanan.*.kantin_produk_id' => ['required', 'exists:kantin_produk,id'],
            'detail_pesanan.*.jumlah' => ['required', 'numeric', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $siswa = Auth::user()->siswa->firstOrFail();
        $fields = $request->all();

        try {
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

            $socketIoService->remindFetch($usaha->user->id);

            return response()->json(['data' => $kantinTransaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('createProdukTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat membuat data produk.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function getKantinTransaksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:aktif,selesai']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $siswa = Auth::user()->siswa->firstOrFail();
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status', 'aktif');

        try {
            $riwayat = $siswa->kantin_transaksi()
                ->select('id', 'usaha_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
                ->with(
                    'usaha:id,nama_usaha', 
                    'kantin_transaksi_detail:id,kantin_transaksi_id,kantin_produk_id,jumlah,harga',
                    'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,deskripsi,harga_jual'
                )
                ->when($status == 'aktif', function ($query) {
                    $query->whereIn('status', ['pending', 'proses', 'siap_diambil']);
                })
                ->when($status == 'selesai', function ($query) {
                    $query->whereIn('status', ['selesai', 'dibatalkan']);
                })
                ->paginate($perPage);

            $riwayat->getCollection()->transform(function ($riwayat) {
                return array_merge(
                    collect($riwayat)->forget(['usaha', 'kantin_transaksi_detail'])->toArray(),
                    $riwayat->usaha->toArray(),
                    ['kantin_transaksi_detail' => $riwayat->kantin_transaksi_detail],
                );
            });

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getKantinRiwayat: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat melihat data kantin riwayat.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}