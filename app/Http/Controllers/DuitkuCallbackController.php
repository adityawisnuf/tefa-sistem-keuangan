<?php

namespace App\Http\Controllers;

use App\Http\Controllers\TopUpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DuitkuCallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        $callbackData = $request->all();
        $type = json_decode($callbackData['additionalParam'], true)['type'];

        if ($type == 'topup') {
            $topUpController = new TopUpController;
            $topUpController->callback($request);
        } else {
            Log::info('bukan topup, gus gas');
        }
    }
}
