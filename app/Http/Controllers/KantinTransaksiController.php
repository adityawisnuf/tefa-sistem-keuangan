<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinTransaksiRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\SiswaWalletRiwayat;
use Illuminate\Database\Eloquent\Model;
use illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
        $validator = Validator::make(request()->all(), [
            'usaha' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:aktif,tidak_aktif'],
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $usaha = Auth::user()->usaha->firstOrFail();
        $status = request('status', 'aktif');
        $perPage = request()->input('per_page', 10);

        try {
            $transaksi = $usaha->kantin_transaksi()
                ->with(['kantin_transaksi_detail.kantin_produk:id,nama_produk', 'siswa:id,nama_depan,nama_belakang'])
                ->when($status ==  'aktif', function ($query) {
                    $query->whereIn('status', ['pending', 'proses', 'siap_diambil']);
                })
                ->when($status ==  'selesai', function ($query) {
                    $query->whereIn('status', ['selesai', 'dibatalkan']);
                })
                ->paginate($perPage);

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('index: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada saat menampilkan data Kantin Transaksi']);
        }
    }

    public function update($id)
    {
        $transaksi = KantinTransaksi::findOrFail($id);
        try {
            $this->statusService->update($transaksi);
            if ($transaksi->status === 'selesai') {
                $transaksi->update(['tanggal_selesai' => now()]);
            }
            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('update: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan Pada saat mengupdate data'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function confirm(KantinTransaksiRequest $request, $id)
    {
        $transaksi = KantinTransaksi::findOrFail($id);
        $fields = $request->validated();

        $siswaWallet = $transaksi->siswa->siswa_wallet;
        $usaha = $transaksi->usaha;

        DB::beginTransaction();
        try {
            $this->statusService->confirmInitialTransaction($fields['confirm'], $transaksi);

            if ($transaksi->status === 'dibatalkan') {
                $harga_total = $transaksi->kantin_transaksi_detail->sum(function ($detail) {
                    return $detail->harga * $detail->jumlah;
                });

                $transaksi->update(['tanggal_selesai' => now()]);
                $usaha->update(['saldo' => $usaha->saldo - $harga_total]);
                $siswaWallet->update(['nominal' => $siswaWallet->nominal + $harga_total]);

                SiswaWalletRiwayat::create([
                    'siswa_wallet_id' => $siswaWallet->id,
                    'merchant_order_id' => null,
                    'tipe_transaksi' => 'pemasukan',
                    'nominal' => $harga_total,
                ]);
            }

            DB::commit();
            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack(); 
            Log::error('confirm: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi Kesalahan pada saat melakukan Confirm Transaksi'], Response::HTTP_INTERNAL_SERVER_ERROR); 
        }
    }

}