<?php

namespace App\Http\Controllers;

use App\Models\PembayaranDuitku;
use Illuminate\Http\Request;

class PembayaranDuitkuController extends Controller
{
    // Menampilkan daftar semua pembayaran Duitku
    public function index()
    {
        $pembayaranDuitku = PembayaranDuitku::all();
        return response()->json([
            'status' => 'success',
            'data' => $pembayaranDuitku
        ], 200);
    }

    // Menyimpan pembayaran Duitku baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'merchant_order_id' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'payment_method' => 'required|string|max:255',
            'transaction_response' => 'required|string',
            'callback_response' => 'required|string',
            'status' => 'required|string|max:50',
        ]);

        $pembayaranDuitku = PembayaranDuitku::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran Duitku berhasil ditambahkan.',
            'data' => $pembayaranDuitku
        ], 201);
    }

    // Memperbarui pembayaran Duitku
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'reference' => 'sometimes|required|string|max:255',
            'payment_method' => 'sometimes|required|string|max:255',
            'transaction_response' => 'sometimes|required|string',
            'callback_response' => 'sometimes|required|string',
            'status' => 'sometimes|required|string|max:50',
        ]);

        $pembayaranDuitku = PembayaranDuitku::where('merchant_order_id',  $id)->first();
        $pembayaranDuitku->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran Duitku berhasil diperbarui.',
            'data' => $pembayaranDuitku
        ], 200);
    }

    // Menghapus pembayaran Duitku
    public function destroy($merchant_order_id)
    {
        $pembayaranDuitku = PembayaranDuitku::where('merchant_order_id', $merchant_order_id)->firstOrFail();
        $pembayaranDuitku->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran Duitku berhasil dihapus.'
        ], 200);
    }

    // Menampilkan pembayaran Duitku berdasarkan merchant_order_id
    public function show($merchant_order_id)
    {
        $pembayaranDuitku = PembayaranDuitku::where('merchant_order_id', $merchant_order_id)->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $pembayaranDuitku
        ], 200);
    }
}
