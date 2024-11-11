<?php

namespace App\Http\Controllers;

use App\Http\Controllers\TopUpController;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DuitkuCallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        $callbackData = $request->all();
        $additionalParam = json_decode($callbackData['additionalParam'], true);
        
        if (isset($additionalParam['type'])) {
            $type = $additionalParam['type'];
            
            if ($type == 'topup') {
                $topUpController = new TopUpController;
                $topUpController->callback($request);
            } else {
                $PembayaranPPDB = new PembayaranController;
                $PembayaranPPDB->handleCallback($request);
            }
        } else {
            $PembayaranPPDB = new PembayaranController;
            $PembayaranPPDB->handleCallback($request);
        }
    }
}