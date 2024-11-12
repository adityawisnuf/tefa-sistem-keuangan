<?php

namespace App\Http\Controllers;

use App\Http\Services\WatZapService as ServicesWatZapService;
use App\Services\WatZapService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected $watZapService;

    public function __construct(ServicesWatZapService $watZapService)
    {
        $this->watZapService = $watZapService;
    }

    /**
     * Send a message to the specified phone number
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'phone_no' => 'required|string',
            'message' => 'required|string',
        ]);

        // Call the service to send the WhatsApp message
        $response = $this->watZapService->sendMessage($request->phone_no, $request->message);

        // Return the response
        return response()->json([
            'status' => isset($response['error']) ? 'failed' : 'success',
            'data' => $response,
        ]);
    }
}
