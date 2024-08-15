<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryLayananRequest;
use App\Models\LaundryLayanan;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Storage;
use Symfony\Component\HttpFoundation\Response;

class LaundryLayananController extends Controller
{
    const IMAGE_STORAGE_PATH = 'public/laundry/layanan/';

    public function index()
    {
        $laundry = Auth::user()->laundry->first();

        $perPage = request()->input('per_page', 10);
        $layanan = $laundry->laundry_layanan()->latest()->paginate($perPage);
        return response()->json(['data' => $layanan], Response::HTTP_OK);
    }

    public function create(LaundryLayananRequest $request)
    {
        $laundry = Auth::user()->laundry->first();
        $fields = $request->validated();
        
        try {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_layanan']);
            $fields['foto_layanan'] = basename($path);
            $fields['laundry_id'] = $laundry->id;
            $layanan = LaundryLayanan::create($fields);
            return response()->json(['data' => $layanan], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan layanan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(LaundryLayanan $layanan)
    {
        return response()->json(['data' => $layanan], Response::HTTP_OK);
    }

    public function update(LaundryLayananRequest $request, LaundryLayanan $layanan)
    {
        $fields = array_filter($request->validated());

        try {
            if (isset($fields['foto_layanan'])) {
                $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_layanan']);
                Storage::delete(self::IMAGE_STORAGE_PATH . $layanan->foto_layanan);
                $fields['foto_layanan'] = basename($path);
            }
            $layanan->update($fields);
            return response()->json(['data' => $layanan], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui layanan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function destroy(LaundryLayanan $layanan)
    {
        try {
            Storage::delete(self::IMAGE_STORAGE_PATH . $layanan->foto_layanan);
            $layanan->delete();
            return response(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus layanan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
