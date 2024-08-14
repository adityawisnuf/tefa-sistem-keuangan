<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinTransaksiRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\Siswa;
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
        $perPage = request()->input('per_page', 10);
        $transaksi = KantinTransaksi::paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function create(KantinTransaksiRequest $request)
    {
        $fields = $request->validated();
        
        try {
            $produk = KantinProduk::find($fields['kantin_produk_id']);
            $siswaWallet = Siswa::find($fields['siswa_id'])->siswa_wallet;

            $fields['harga'] = $produk->harga;
            $fields['harga_total'] = $fields['harga'] * $fields['jumlah'];

            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json(['message' => 'Saldo tidak mencukupi'], Response::HTTP_BAD_REQUEST);
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
        return response()->json($result['message'], $result['statusCode']);
    }
    
    public function confirmInitialTransaction(KantinTransaksiRequest $request, KantinTransaksi $transaksi)
    {
        $fields = $request->validated();

        try {
            if (in_array($transaksi['status'], ['dibatalkan', 'selesai'])) {
                return response()->json([
                    'message' => 'Pesanan sudah selesai!',
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            $siswaWallet = Siswa::find($transaksi['siswa_id'])->siswa_wallet;
            
            DB::beginTransaction();
            $transaksi->update($fields);
            if ($fields['status'] == 'dibatalkan') {
                $siswaWallet->update([
                    'nominal' => $siswaWallet->nominal + $transaksi['harga_total']
                ]);
            }
            DB::commit();

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengkonfirmasi transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
