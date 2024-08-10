<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $daftarPembayaran = Pembayaran::all();
        return response()->json($daftarPembayaran);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $siswaId = $request->get('siswa_id');
        $pembayaranKategoriId = $request->get('pembayaran_kategori_id');
        $nominal = $request->get('nominal');
        $status = $request->get('status');
        $kelasId = $request->get('kelas_id');

        $pembayaran = Pembayaran::create([
            'siswa_id' => $siswaId,
            'pembayaran_kategori_id' => $pembayaranKategoriId,
            'nominal' => $nominal,
            'status' => $status,
            'kelas_id' => $kelasId
        ]);

        return response()->json($pembayaran);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $pembayaran = Pembayaran::where('id', $id)->first();
        return response()->json($pembayaran);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pembayaran = Pembayaran::find($id);

        $siswaId = $request->get('siswa_id');
        $pembayaranKategoriId = $request->get('pembayaran_kategori_id');
        $nominal = $request->get('nominal');
        $status = $request->get('status');
        $kelasId = $request->get('kelas_id');

        $pembayaran->update([
            'siswa_id' => $siswaId,
            'pembayaran_kategori_id' => $pembayaranKategoriId,
            'nominal' => $nominal,
            'status' => $status,
            'kelas_id' => $kelasId
        ]);

        return response()->json($pembayaran);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pembayaran = Pembayaran::find($id);

        $pembayaran->delete();

        return response()->json(['pesan' => 'pembayaran berhasil dihapus!']);
    }
}
