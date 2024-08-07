<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryItemRequest;
use App\Models\LaundryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LaundryItemController extends Controller
{
    const IMAGE_STORAGE_PATH = 'public/laundry/item';

    public function index()
    {
        $items = LaundryItem::latest()->paginate(10);
        return response()->json([
            'data' => $items,
            'message' => 'List item.'
        ], 200);
    }

    public function create(LaundryItemRequest $request)
    {
        $fields = $request->validated();

        $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_item']);
        $fields['foto'] = basename($path);
        $item = LaundryItem::create($fields);

        return response()->json([
            'data' => $item,
            'message' => 'Item created.'
        ], 201);
    }

    public function update(LaundryItemRequest $request, LaundryItem $item)
    {
        $fields = array_filter($request->validated());

        if (isset($fields['foto_item'])) {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_item']);
            Storage::delete(self::IMAGE_STORAGE_PATH . $item->foto_item);
            $fields['foto_item'] = basename($path);
        }

        $item->update($fields);

        return response()->json([
            'data' => $item,
            'message' => 'Item updated.'
        ], 200);
    }
    
    public function destroy(LaundryItem $item)
    {
        Storage::delete(self::IMAGE_STORAGE_PATH . $item->foto_item);
        $item->delete();
        
        return response()->json([
            'data' => [],
            'message' => 'Item deleted.'
        ], 204);
    }
}
