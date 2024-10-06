<?php

namespace App\Http\Controllers;

use App\Http\Services\SocketIOService;
use App\Models\LaundryTransaksi;
use App\Models\SiswaWalletRiwayat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LaundryTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:aktif,selesai'],
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        $usaha = Auth::user()->usaha;
        $status = $validated['status'] ?? 'aktif';
        $perPage = $validated['per_page'] ?? 10;

        $transaksi = $usaha->laundry_transaksi()
            ->select('id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'laundry_transaksi_detail:id,laundry_transaksi_id,laundry_layanan_id,jumlah,harga',
                'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,deskripsi,harga',
                'siswa:id,nama_depan,nama_belakang'
            )
            ->when($status == 'aktif', function ($query) {
                $query->whereIn('status', ['pending', 'proses', 'siap_diambil']);
            })
            ->when($status == 'selesai', function ($query) {
                $query->whereIn('status', ['selesai', 'dibatalkan']);
            })
            ->paginate($perPage);

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
    
    public function show(Request $request, LaundryTransaksi $transaksi)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $data = $transaksi
            ->laundry_transaksi_detail()
            ->select('id', 'laundry_layanan_id', 'laundry_transaksi_id', 'jumlah', 'harga')
            ->with('laundry_layanan:id,nama_layanan,foto_layanan,deskripsi,harga,tipe,satuan')
            ->paginate($perPage);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    public function update(LaundryTransaksi $transaksi, SocketIOService $socketIOService)
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

    public function confirm(Request $request, LaundryTransaksi $transaksi, SocketIOService $socketIOService)
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
            $harga_total = $transaksi->laundry_transaksi_detail->sum(function ($detail) {
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