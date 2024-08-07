<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryItemRequest;
use App\Models\LaundryItem;
use Illuminate\Http\Request;

class LaundryItemController extends Controller
{
    public function index()
    {
        $items = LaundryItem::latest()->paginate(10);
        return response()->json(['data' => $items], 200);
    }
}
