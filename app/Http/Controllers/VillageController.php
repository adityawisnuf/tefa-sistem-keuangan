<?php

namespace App\Http\Controllers;

use App\Models\Village;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $villages = Village::all();

        return response()->json([
            'success' => true,
            'message' => 'Data desa berhasil ditampilkan',
            'data' => $villages
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $villages = Village::find($id);

        if (!$villages) {
            return response()->json([
                'success' => false,
                'message' => 'Data desa gagal ditampilkan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data desa berhasil ditampilkan',
            'data' => $villages
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
