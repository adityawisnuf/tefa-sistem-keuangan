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
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_produk' => ['nullable', 'string'],
            'nama_kategori' => ['nullable', 'string']
        ]);

        $nama_produk = $validated['nama_produk'] ?? null;
        $nama_kategori = $validated['nama_kategori'] ?? null;
        $perPage = $validated['per_page'] ?? 10;

        $produk = KantinProduk
            ::select('id', 'nama_produk', 'foto_produk', 'deskripsi', 'harga_jual', 'stok')
            ->where('status', 'aktif')
            ->when($nama_produk, function ($query) use ($nama_produk) {
                $query->where('nama_produk', 'like', "%$nama_produk%");
            })
            ->when($nama_kategori, function ($query) use ($nama_kategori) {
                $query->whereRelation('kantin_produk_kategori', 'nama_kategori', 'like', "%$nama_kategori%");
            })
            ->latest()
            ->paginate($perPage);

        return response()->json(['data' => $produk], Response::HTTP_OK);
    }


    public function show(KantinProduk $produk)
    {
        if ($produk->status == 'aktif')
            return response()->json(['data' => $produk], Response::HTTP_OK);
        return response()->json(['data' => 'produk tidak tersedia'], Response::HTTP_BAD_REQUEST);
    }

    public function createProdukTransaksi(Request $request, SocketIoService $socketIoService)
    {
        $validated = $request->validate([
            'detail_pesanan' => ['required', 'array', 'min:1'],
            'detail_pesanan.*.kantin_produk_id' => ['required', 'exists:kantin_produk,id'],
            'detail_pesanan.*.jumlah' => ['required', 'numeric', 'min:1'],
        ]);

        $siswa = Auth::user()->siswa;

        $productIds = collect($validated['detail_pesanan'])->pluck('kantin_produk_id')->toArray();
        $products = KantinProduk::whereIn('id', $productIds)->get();

        $usaha = $products->first()->usaha;
        $siswaWallet = $siswa->siswa_wallet;

        $validated['siswa_id'] = $siswa->id;
        $validated['usaha_id'] = $usaha->id;

        DB::beginTransaction();
        $kantinTransaksi = KantinTransaksi::create($validated);
        $totalHarga = 0;

        foreach ($validated['detail_pesanan'] as $productDetail) {
            $product = $products->firstWhere('id', $productDetail['kantin_produk_id']);
            $qty = $productDetail['jumlah'];

            if ($product->stok < $qty)
                return response()->json(['message' => "Stok produk {$product->nama_produk} tidak mencukupi."], Response::HTTP_BAD_REQUEST);

            $product->stok -= $qty;

            KantinTransaksiDetail::create([
                'kantin_produk_id' => $product->id,
                'kantin_transaksi_id' => $kantinTransaksi->id,
                'jumlah' => $qty,
                'harga' => $product->harga_jual,
            ]);

            $totalHarga += $product->harga_jual * $qty;
        }

        if ($siswaWallet->nominal < $totalHarga)
            return response()->json(['message' => 'Saldo tidak mencukupi untuk transaksi ini.'], Response::HTTP_BAD_REQUEST);

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
    }



    public function getKantinTransaksi(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:aktif,selesai']
        ]);

        $siswa = Auth::user()->siswa;
        $perPage = $validated['per_page'] ?? 10;
        $status = $validated['status'] ?? 'aktif';

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
                collect($riwayat)->forget(['usaha'])->toArray(),
                collect($riwayat->usaha)->forget('id')->toArray(),
            );
        });

        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }   
}