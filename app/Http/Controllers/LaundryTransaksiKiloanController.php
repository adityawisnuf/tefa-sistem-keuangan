<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryTransaksiKiloanRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksiKiloan;
use App\Models\LaundryTransaksiSatuan;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LaundryTransaksiKiloanController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }

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

    public function update(LaundryTransaksiKiloan $transaksi)
    {
        $result = $this->statusService->update($transaksi);
        return response()->json($result['message'], $result['statusCode']);
    }
    
    public function confirmInitialTransaction(LaundryTransaksiKiloanRequest $request, LaundryTransaksiKiloan $transaksi)
    {
        $fields = $request->validated();
        $result = $this->statusService->confirmInitialTransaction($fields, $transaksi);
        return response()->json($result['message'], $result['statusCode']);
    }
}
