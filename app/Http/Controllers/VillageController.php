<?php

namespace App\Http\Controllers;

use App\Models\Regency;
use App\Models\Village;
use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->getProvince();
    }

    public function getProvince()
    {
        $provinces = Province::all();

        return response()->json([
            'success' => true,
            'message' => 'Data provinsi berhasil ditampilkan',
            'data' => $provinces
        ]);
    }

    public function getRegency($id)
    {
        $regencies = Regency::where('province_id', $id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kabupaten/kota berhasil ditampilkan',
            'data' => $regencies
        ]);
    }

    public function getDistrict($id)
    {
        $districts = District::where('regency_id', $id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kecamatan/desa berhasil ditampilkan',
            'data' => $districts
        ]);
    }

    public function getVillage($id)
    {
        $villages = Village::where('district_id', $id)->get();

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
