<?php

namespace App\Http\Controllers;

use App\Http\Services\SocketIOService;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksi;
use App\Models\LaundryTransaksiDetail;
use App\Models\SiswaWalletRiwayat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SiswaLaundryController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
            'tipe' => ['nullable', 'string', 'in:kiloan,satuan'],
            'nama_layanan' => ['nullable', 'string']
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $tipe = $validated['tipe'] ?? 'kiloan';
        $namaLayanan = $validated['nama_layanan'] ?? null;

        $items = LaundryLayanan
            ::where('status', 'aktif')
            ->where('tipe', $tipe)
            ->when($namaLayanan, function ($query) use ($namaLayanan) {
                $query->where('nama_layanan', 'like', "%$namaLayanan%");
            })
            ->latest()
            ->paginate($perPage);

        return response()->json(['data' => $items], Response::HTTP_OK);
    }

    public function show(LaundryLayanan $layanan)
    {
        if ($layanan->status == 'aktif')
            return response()->json(['data' => $layanan], Response::HTTP_OK);
        return response()->json(['message' => 'Produk tidak tersedia.'], Response::HTTP_BAD_REQUEST);
    }

    public function getLayananTransaksi(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:aktif,selesai']
        ]);

        $siswa = Auth::user()->siswa;
        $perPage = $validated['per_page'] ?? 10;
        $status = $validated['status'] ?? 'aktif';

        $riwayat = $siswa->laundry_transaksi()
            ->select('id', 'usaha_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'usaha:id,nama_usaha',
                'laundry_transaksi_detail:id,laundry_transaksi_id,laundry_layanan_id,jumlah,harga',
                'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,deskripsi,harga'
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

    public function createLayananTransaksi(Request $request, SocketIOService $socketIOService)
    {
        $siswa = Auth::user()->siswa;
        $validated = $request->validate([
            'detail_pesanan' => ['required', 'array', 'min:1'],
            'detail_pesanan.*.laundry_layanan_id' => ['required', 'exists:laundry_layanan,id'],
            'detail_pesanan.*.jumlah' => ['required', 'numeric', 'min:1'],
        ]);

        $usaha = LaundryLayanan::find($validated['detail_pesanan'][0]['laundry_layanan_id'])->usaha;
        $siswaWallet = $siswa->siswa_wallet;

        $validated['siswa_id'] = $siswa->id;
        $validated['usaha_id'] = $usaha->id;

        DB::beginTransaction();
        $laundryTransaksi = LaundryTransaksi::create($validated);
        $totalHarga = 0;

        foreach ($validated['detail_pesanan'] as $layananDetail) {
            $layanan = $usaha->laundry_layanan()->findOrFail($layananDetail['laundry_layanan_id']);
            $qty = $layananDetail['jumlah'];

            LaundryTransaksiDetail::create([
                'laundry_layanan_id' => $layanan->id,
                'laundry_transaksi_id' => $laundryTransaksi->id,
                'jumlah' => $qty,
                'harga' => $layanan->harga,
            ]);

            $totalHarga += $layanan->harga * $layananDetail['jumlah'];
        }

        if ($siswaWallet->nominal < $totalHarga)
            return response()->json(['message' => 'Saldo tidak mencukupi untuk transaksi ini.'], Response::HTTP_BAD_REQUEST);

        $usaha->update([
            'saldo' => $usaha->saldo + $totalHarga,
        ]);

        $siswaWallet->update([
            'nominal' => $siswaWallet->nominal - $totalHarga,
        ]);

        SiswaWalletRiwayat::create([
            'siswa_wallet_id' => $siswaWallet->id,
            'merchant_order_id' => null,
            'tipe_transaksi' => 'pengeluaran',
            'nominal' => $totalHarga,
        ]);
        DB::commit();

        $socketIOService->remindFetch($usaha->user->id);

        return response()->json(['data' => $laundryTransaksi], Response::HTTP_CREATED);
    }
}
