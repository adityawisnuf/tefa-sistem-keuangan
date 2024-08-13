<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryTransaksiSatuanRequest;
use App\Models\LaundryItem;
use App\Models\LaundryTransaksiSatuan;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LaundryTransaksiSatuanController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $transaksi = LaundryTransaksiSatuan::paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function create(LaundryTransaksiSatuanRequest $request)
    {
        $fields = $request->validated();

        $layanan = LaundryItem::find($fields['laundry_item_id']);
        $fields['harga'] = $layanan->harga;
        $fields['laundry_id'] = $layanan->laundry_id;
        $fields['harga_total'] = $fields['harga'] * $fields['jumlah_item'];
        try {
            $transaksi = LaundryTransaksiSatuan::create($fields);
            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // public function update()
    // {
    //     'status' => proses, dibatalkan <=

    //     'status' => proses => siap_diambil => selesai
    // }
}
