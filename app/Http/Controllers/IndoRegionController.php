<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Http\Request;

class IndoRegionController extends Controller
{
    public function getAllProvinces()
    {
        $provinces = Province::all();
        return response()->json($provinces);
    }

    public function getRegenciesByProvince($provinceId)
    {
        $regencies = Regency::where('province_id', $provinceId)->get();
        return response()->json($regencies);
    }

    public function getDistrictsByRegency($regencyId)
    {
        $districts = District::where('regency_id', $regencyId)->get();
        return response()->json($districts);
    }

    public function getVillagesByDistrict($districtId)
    {
        $villages = Village::where('district_id', $districtId)->get();
        return response()->json($villages);
    }
}
