<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Services\DuitkuService;
use Illuminate\Http\Request;

class TopUpController extends Controller
{
    protected $duitkuService;

    public function __construct()
    {
        $this->duitkuService = new DuitkuService();
    }

    public function getPaymentMethod()
    {
        $result = $this->duitkuService->getPaymentMethod();
        return response()->json($result['data'], $result['statusCode']);
    }
    
    public function requestTransaction(TransactionRequest $request)
    {
        $result = $this->duitkuService->requestTransaction($request->validated());
        return response()->json($result['data'], $result['statusCode']);
    }
    
    public function callback()
    {
        $this->duitkuService->callback(request()->all());
        return response()->json('terpanggil', 200);
    }
}
