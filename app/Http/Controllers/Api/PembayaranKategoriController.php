<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranKategoriResource;
use App\Models\PembayaranKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembayaranKategoriController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $query = PembayaranKategori::oldest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('jenis_pembayaran', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
            });
        }

        $kategori = $request->input('page') === 'all' ? $query->get() : $query->paginate(5);

        return new PembayaranKategoriResource(true, 'List Pembayaran Kategori', $kategori);
    }

    /**
     * store
     *
     * @param  mixed  $request
     * @return void
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jenis_pembayaran' => 'required|in:1,2',
            'tanggal_pembayaran' => 'required|string|max:255|regex:/^\d{2}(-\d{2})?$/',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kategori = PembayaranKategori::create($validator->validated());

        return new PembayaranKategoriResource(true, ' Pembayaran Kategori Baru Berhasil Ditambahkan', $kategori);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jenis_pembayaran' => 'required|in:1,2',
            'tanggal_pembayaran' => 'required|string|max:255|regex:/^\d{2}(-\d{2})?$/',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kategori = PembayaranKategori::findOrFail($id);
        $kategori->update($validator->validated());

        return new PembayaranKategoriResource(true, 'Kategori Berhasil Diubah', $kategori);
    }

    public function destroy($id)
    {
        $kategori = PembayaranKategori::findOrFail($id);
        $kategori->delete();

        return new PembayaranKategoriResource(true, 'Data Kategori Berhasil Dihapus', $kategori);
    }
}
