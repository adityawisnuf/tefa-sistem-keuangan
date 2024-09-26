<?php

namespace App\Http\Controllers;

use App\Models\Ppdb;
use Illuminate\Http\Request;

class PpdbController extends Controller
{
    // Tampilkan semua data PPDB
    public function index()
    {
        $ppdb = Ppdb::all();
        return response()->json($ppdb);
    }

    // Menyimpan data baru ke tabel PPDB
    public function store(Request $request)
    {
        $request->validate([
            'status' => 'required|integer|in:1,2,3',
            'merchant_order_id' => 'required|string',
        ]);

        $ppdb = Ppdb::create([
            'status' => $request->status,
            'merchant_order_id' => $request->merchant_order_id,
        ]);

        return response()->json(['message' => 'PPDB created successfully!', 'ppdb' => $ppdb], 201);
    }

    // Update data PPDB berdasarkan ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:1,2,3',
            'merchant_order_id' => 'required|string',
        ]);

        $ppdb = Ppdb::findOrFail($id);
        $ppdb->update([
            'status' => $request->status,
            'merchant_order_id' => $request->merchant_order_id,
        ]);

        return response()->json(['message' => 'PPDB updated successfully!', 'ppdb' => $ppdb], 200);
    }

    // Hapus data PPDB berdasarkan ID
    public function destroy($id)
    {
        $ppdb = Ppdb::findOrFail($id);
        $ppdb->delete();

        return response()->json(['message' => 'PPDB deleted successfully!'], 200);
    }

    // Tampilkan data PPDB berdasarkan ID
    public function show($id)
    {
        $ppdb = Ppdb::findOrFail($id);
        return response()->json($ppdb);
    }
}

