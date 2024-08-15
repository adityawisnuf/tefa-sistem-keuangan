<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinTransaksiRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class KantinTransaksiController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }

    public function index()
    {
        $kantin = Auth::user()->kantin->first();

        $perPage = request()->input('per_page', 10);
        $transaksi = $kantin->kantin_transaksi()->paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function create(KantinTransaksiRequest $request)
    {
        $fields = $request->validated();
        $siswa = Auth::user()->siswa->first();
        $siswaWallet = $siswa->siswa_wallet;

        $produk = KantinProduk::find($fields['kantin_produk_id']);
        $fields['harga'] = $produk->harga;
        $fields['kantin_id'] = $produk->kantin_id;
        $fields['harga_total'] = $fields['harga'] * $fields['jumlah'];

        try {
            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk transaksi ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();
            $transaksi = KantinTransaksi::create($fields);
            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal - $fields['harga_total']
            ]);
            DB::commit();

            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(KantinTransaksi $transaksi)
    {
        $result = $this->statusService->update($transaksi);
        if ($result['statusCode'] === Response::HTTP_OK && $transaksi->status === 'selesai') {
            $transaksi->update(['tanggal_selesai' => now()]);
        }
        return response()->json($result['message'], $result['statusCode']);
    }
    
    public function confirmInitialTransaction(KantinTransaksiRequest $request, KantinTransaksi $transaksi)
    {
        $fields = $request->validated();
        $siswaWallet = $transaksi->siswa->siswa_wallet;
        
        try {
            $result = $this->statusService->confirmInitialTransaction($fields, $transaksi);
            if ($result['statusCode'] === Response::HTTP_OK && $transaksi->status === 'dibatalkan') {
                $siswaWallet->update([
                    'nominal'=> $siswaWallet->nominal + $transaksi->harga_total
                ]);
            }
            return response()->json($result['message'], $result['statusCode']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal mengubah status transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
