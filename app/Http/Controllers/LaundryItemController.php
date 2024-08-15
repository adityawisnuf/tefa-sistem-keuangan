<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryItemRequest;
use App\Models\LaundryItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class LaundryItemController extends Controller
{
    const IMAGE_STORAGE_PATH = 'public/laundry/item/';

    public function index()
    {
        $laundry = Auth::user()->laundry->first();

        $perPage = request()->input('per_page', 10);
        $items = $laundry->laundry_item()->latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }

    public function create(LaundryItemRequest $request)
    {
        $laundry = Auth::user()->laundry->first();
        $fields = $request->validated();

        try {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_item']);
            $fields['foto_item'] = basename($path);
            $fields['laundry_id'] = $laundry->id;
            $item = LaundryItem::create($fields);
            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan item: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(LaundryItem $item)
    {
        return response()->json(['data' => $item], Response::HTTP_OK);
    }

    public function update(LaundryItemRequest $request, LaundryItem $item)
    {
        $fields = array_filter($request->validated());

        try {
            if (isset($fields['foto_item'])) {
                $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_item']);
                Storage::delete(self::IMAGE_STORAGE_PATH . $item->foto_item);
                $fields['foto_item'] = basename($path);
            }
            $item->update($fields);
            return response()->json(['data' => $item], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui item: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function destroy(LaundryItem $item)
    {
        try {
            Storage::delete(self::IMAGE_STORAGE_PATH . $item->foto_item);
            $item->delete();
            return response(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus item: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
