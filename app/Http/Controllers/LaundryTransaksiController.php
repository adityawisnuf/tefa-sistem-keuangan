<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryTransaksiKiloanRequest;
use App\Http\Requests\LaundryTransaksiRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksi;
use App\Models\LaundryTransaksiKiloan;
use App\Models\Siswa;
use App\Models\SiswaWalletRiwayat;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LaundryTransaksiController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }
    //get done
    public function getTransaction()
    {
        $validator = Validator::make(request()->all(), [
            'usaha' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:pending,proses,siap_diambil,selesai,dibatalkan'],
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $usaha = Auth::user()->usaha->firstOrFail();
        $status = request('status', 'aktif');
        $perPage = request('per_page', 10);

        try {
            $transaksi = $usaha->laundry_transaksi()
                ->with(['siswa:id,nama_depan,nama_belakang', 'laundry_transaksi_detail.laundry_layanan'])
                ->withSum('laundry_transaksi_detail as harga_total', 'harga', 'total_harga') // Tambahkan baris ini
                ->when($status == 'aktif', function ($query) {
                    $query->whereIn('status', ['pending', 'proses', 'siap_diambil']);
                })
                ->when($status == 'selesai', function ($query) {
                    $query->whereIn('status', ['selesai', 'dibatalkan']);
                })
                ->paginate($perPage);

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getTransaction: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan ketika menampilkan data transaksi'], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showLaundry($id)
    {
        $usaha = Auth::user()->usaha->first();

        try {
            $transaksi = LaundryTransaksi::where('usaha_id', $usaha->id)
                ->where('id', $id)
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses ke transaksi ini.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('showLaundry: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada saat akan menampilkan data laundry'], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($id)
    {
        $transaksi = LaundryTransaksi::findOrFail($id);
        try {
            $this->statusService->update($transaksi);
            if ($transaksi->status === 'selesai') {
                $transaksi->update(['tanggal_selesai' => now()]);
            }
            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('update: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada saat akan  update data laundry'], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function confirm(LaundryTransaksiRequest $request, $id)
    {
        $fields = $request->validated();
        $transaksi = LaundryTransaksi::findOrFail($id);

        $siswaWallet = $transaksi->siswa->siswa_wallet;
        $usaha = $transaksi->usaha;

        DB::beginTransaction();
        try {

            $this->statusService->confirmInitialTransaction($fields['confirm'], $transaksi);

            if ($transaksi->status === 'dibatalkan') {
                $harga_total = $transaksi->laundry_transaksi_detail->sum(function ($det ail) {
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
            Log::error('confirmInitialTransaction: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada saat akan confirmInitialTransaction data laundry'], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDetailUsahaTransaksi($id)
    {
        $validator = Validator::make(request()->all(), [
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        
        $perPage = request('per_page', 10);

        try {
            $transaksi = LaundryTransaksi::find($id)
                ->laundry_transaksi_detail()
                ->with(['laundry_layanan'])
                ->paginate($perPage);

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getDetailUsahaTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada saat akan getDetailUsahaTransaksi data laundry'], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}