<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinTransaksiRequest;
use App\Http\Services\SocketIOService;
use App\Models\KantinTransaksi;
use App\Models\SiswaWalletRiwayat;
use Illuminate\Http\Request;
use illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class KantinTransaksiController extends Controller
{
    protected $statusService;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'usaha' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:aktif,selesai'],
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        $usaha = Auth::user()->usaha;
        $status = $validated['status'] ?? 'aktif';
        $perPage = $validated['per_page'] ?? 10;

        $transaksi = $usaha->kantin_transaksi()
            ->select('id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'kantin_transaksi_detail:id,kantin_transaksi_id,kantin_produk_id,jumlah,harga',
                'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,deskripsi,harga_jual',
                'siswa:id,nama_depan,nama_belakang'
            )
            ->when($status == 'aktif', function ($query) {
                $query->whereIn('status', ['pending', 'proses', 'siap_diambil']);
            })
            ->when($status == 'selesai', function ($query) {
                $query->whereIn('status', ['selesai', 'dibatalkan']);
            })
            ->paginate($perPage);

        $transaksi->getCollection()->transform(function ($transaksi) {
            return array_merge(
                collect($transaksi)->forget(['kantin_transaksi_detail', 'siswa'])->toArray(),
                ['nama_siswa' => $transaksi->siswa->nama_siswa],
                ['kantin_transaksi_detail' => $transaksi->kantin_transaksi_detail],
            );
        });

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function update(KantinTransaksi $transaksi, SocketIOService $socketIOService)
    {
        if ($transaksi->status == 'selesai' || $transaksi->status == 'dibatalkan')
            return response()->json(['message' => 'Pesanan sudah selesai!'], Response::HTTP_BAD_REQUEST);

        switch ($transaksi->status) {
            case 'proses':
                $transaksi->update(['status' => 'siap_diambil']);
                break;
            case 'siap_diambil':
                $transaksi->update([
                    'status' => 'selesai',
                    'tanggal_selesai' => now()
                ]);
                break;
            default:
                return response()->json(['message' => 'Status tidak valid!'], Response::HTTP_BAD_REQUEST);
        }

        $socketIOService->remindFetch($transaksi->siswa->user->id);

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function confirm(Request $request, KantinTransaksi $transaksi, SocketIOService $socketIOService)
    {
        $validated = $request->validate([
            'confirm' => ['required', 'boolean']
        ]);

        $siswaWallet = $transaksi->siswa->siswa_wallet;
        $usaha = $transaksi->usaha;

        if ($transaksi->status != 'pending')
            return response()->json(['message' => 'Pesanan sudah dikonfirmasi!'], Response::HTTP_BAD_REQUEST);

        DB::beginTransaction();

        $transaksi->update([
            'status' => $validated['confirm'] ? 'proses' : 'dibatalkan'
        ]);

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

        $socketIOService->remindFetch($transaksi->siswa->user->id);

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
}