<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryItemRequest;
use App\Models\LaundryItem;
use App\Models\LaundryItemDetail;
use Illuminate\Http\Request;
use Exception;

use Symfony\Component\HttpFoundation\Response;

class LaundryItemDetailController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $transaksi = LaundryItemDetail::paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function create(LaundryItemRequest $request)
    {
        $fields = $request->validated();

        $item = LaundryItem::find($fields['laundry_Item_id']);
        $fields['harga'] = $item->harga;
        $fields['harga_total'] = $fields['harga'] * $fields['jumlah'];
        try {
            $transaksi = LaundryItemDetail::create($fields);
            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat transaksi detail: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
