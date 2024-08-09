<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryTransaksiKiloanRequest;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksiKiloan;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LaundryTransaksiKiloanController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $transaksi = LaundryTransaksiKiloan::paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function create(LaundryTransaksiKiloanRequest $request)
    {
        $fields = $request->validated();
        
        $layanan = LaundryLayanan::find($fields['laundry_layanan_id']);
        $fields['harga'] = $layanan->harga_per_kilo;
        $fields['harga_total'] = $fields['harga'] * $fields['berat'];
        try {
            $transaksi = LaundryTransaksiKiloan::create($fields);
            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
