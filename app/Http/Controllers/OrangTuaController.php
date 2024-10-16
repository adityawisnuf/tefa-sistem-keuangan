<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Orangtua;

class OrangTuaController extends Controller
{
    public function index()
    {
        try {
            $orangtuashow = Orangtua::all();

            return response()->json([
                'success' => true,
                'message' => 'sekolah berhasil ditampilkan',
                'data' => $orangtuashow
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data sekolah',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        // Find the sekolah by ID
        $orangtua = Orangtua::find($id);

        if (!$orangtua) {
            return response()->json([
                'success' => false,
                'message' => 'Data Orangtua tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Orangtua berhasil ditampilkan',
            'data' => $orangtua
        ], 200);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'nama' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $orangtua = Orangtua::create([

            'nama' => $request->nama,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'orangtua berhasil ditambahkan',
            'data' => $orangtua
        ]);

    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable',
            'nama' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $orangtua = Orangtua::find($id);

        if (!$orangtua) {
            return response()->json([
                'success' => false,
                'message' => 'orangtua tidak ditemukan'
            ], 404);
        }

        if ($request->user_id) {
            $orangtua->update([
                'user_id' => $request->user_id,
                'nama' => $request->nama,

            ]);
        } else {
            $orangtua->update([

                'nama' => $request->nama,

            ]);
        }



        return response()->json([
            'success' => true,
            'message' => 'orangtua berhasil diperbarui',
            'data' => $orangtua
        ]);
    }

    public function destroy($id)
    {
        $orangtua = Orangtua::find($id);

        if (!$orangtua) {
            return response()->json([
                'success' => false,
                'message' => 'orangtua tidak ditemukan'
            ], 404);
        }

        $orangtua->delete();

        return response()->json([
            'success' => true,
            'message' => 'orangtua berhasil dihapus'
        ]);
    }

    public function getSiswa()
    {
        $orangtua = Auth::user()->orangtua;
        $siswa = $orangtua->siswa()->select('id', 'nama_depan', 'nama_belakang')->get();

        return response()->json(['data' => $siswa], Response::HTTP_OK);
    }

    public function getRiwayatWalletSiswa(Request $request, $id)
    {
        $orangtua = Auth::user()->orangtua;

        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
        ]);

        $startDate = $validated['tanggal_awal'] ?? null;
        $endDate = $validated['tanggal_akhir'] ?? null;

        $siswa = $orangtua->siswa()
            ->with([
                'siswa_wallet',
                'siswa_wallet.siswa_wallet_riwayat' => function ($query) use ($startDate, $endDate) {
                    $query
                        ->select(
                            'siswa_wallet_id',
                            DB::raw('SUM(CASE WHEN tipe_transaksi = "pemasukan" THEN nominal ELSE 0 END) as total_pemasukan'),
                            DB::raw('SUM(CASE WHEN tipe_transaksi = "pengeluaran" THEN nominal ELSE 0 END) as total_pengeluaran'),
                        )
                        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                            $query->whereBetween('tanggal_riwayat', [
                                Carbon::parse($startDate)->startOfDay(),
                                Carbon::parse($endDate)->endOfDay()
                            ]);
                        })
                        ->groupBy('siswa_wallet_id');
                }
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $siswa->id,
                'nama_siswa' => $siswa->nama_siswa,
                'saldo_siswa' => $siswa->siswa_wallet->nominal,
                'total_pemasukan' => $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pemasukan ?? 0,
                'total_pengeluaran' => $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pengeluaran ?? 0,
            ]
        ], Response::HTTP_OK);
    }

    public function getRiwayatKantinSiswa(Request $request, $id)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $orangTua = Auth::user()->orangtua;
        $perPage = $validated['per_page'] ?? 10;

        $riwayat = $orangTua
            ->siswa()
            ->findOrFail($id)
            ->kantin_transaksi()
            ->select('id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'kantin_transaksi_detail:id,kantin_transaksi_id,kantin_produk_id,jumlah,harga',
                'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,harga_jual'
            )
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->paginate($perPage);

        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
}