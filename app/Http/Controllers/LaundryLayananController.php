<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryLayananRequest;
use App\Models\LaundryLayanan;
use illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class LaundryLayananController extends Controller
{
    const IMAGE_STORAGE_PATH = 'public/laundry/layanan/';

    public function index(Request $request)
    {
        $validated = $request->validate([
            'usaha' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_layanan' => ['nullable', 'string']
        ]);

        $usaha = Auth::user()->usaha;
        $perPage = $validated['per_page'] ?? 10;
        $namaLayanan = $validated['nama_layanan'] ?? null;

        $layanan = $usaha
            ->laundry_layanan()
            ->when($namaLayanan, function ($query) use ($namaLayanan) {
                $query->where('nama_layanan', 'like', "%$namaLayanan%");
            })
            ->paginate($perPage);
        return response()->json(['data' => $layanan], Response::HTTP_OK);
    }

    public function create(Request $request)
    {

        $validated = $request->validate([
            'nama_layanan' => ['required', 'string', 'max:255'],
            'foto_layanan' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'tipe' => ['required', 'in:satuan,kiloan'],
            'status' => ['nullable', 'in:aktif,tidak_aktif'],
        ]);

        $usaha = Auth::user()->usaha->first();

        $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $validated['foto_layanan']);
        $validated['foto_layanan'] = basename($path);
        $validated['usaha_id'] = $usaha->id;
        $validated['satuan'] = $validated['tipe'] == 'satuan' ? 'pcs' : 'kg';
        $layanan = LaundryLayanan::create($validated);
        return response()->json(['data' => $layanan], Response::HTTP_CREATED);
    }

    public function show(LaundryLayanan $layanan)
    {
        return response()->json(['data' => $layanan], Response::HTTP_OK);
    }

    public function update(LaundryLayananRequest $request, LaundryLayanan $layanan)
    {
        $validated = $request->validate([
            'nama_layanan' => ['sometimes', 'nullable', 'string', 'max:255'],
            'foto_layanan' => ['sometimes', 'nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['sometimes', 'nullable', 'string'],
            'harga' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'tipe' => ['sometimes', 'nullable', 'in:satuan,kiloan'],
            'status' => ['sometimes', 'nullable', 'in:aktif,tidak_aktif'],
        ]);

        $fields = array_filter($validated);

        if (isset($fields['foto_layanan'])) {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_layanan']);
            Storage::delete(self::IMAGE_STORAGE_PATH . $layanan->foto_layanan);
            $fields['foto_layanan'] = basename($path);
        }

        $fields['satuan'] = $fields['tipe'] == 'satuan' ? 'pcs' : 'kg';
        $layanan->update($fields);

        return response()->json(['data' => $layanan], Response::HTTP_OK);
    }

    public function destroy(LaundryLayanan $layanan)
    {
        $layanan->delete();
        Storage::delete(self::IMAGE_STORAGE_PATH . $layanan->foto_layanan);

        return response()->json(['message' => 'Data berhasil dihapus.'], Response::HTTP_OK);
    }
}