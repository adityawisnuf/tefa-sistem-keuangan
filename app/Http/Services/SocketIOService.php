<?php

namespace App\Http\Services;
use GuzzleHttp\Client;

class SocketIOService
{
    public function remindFetch($userId)
    {
        $client = new Client();
        $client->post(env('WEBSOCKET_URL') . '/remind-fetch', [
            'json' => [
                'userId' => $userId
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'Application' => 'application/json',
            ]
        ]);
    }
}