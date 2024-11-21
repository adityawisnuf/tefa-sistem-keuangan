<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UsahaController extends Controller
{
    public function getStatusBuka() {
        $statusBuka = Auth::user()->usaha->status_buka;
        return response()->json(['data' => $statusBuka], Response::HTTP_OK);
    }

    public function updateStatusBuka() {
        $usaha = Auth::user()->usaha;

        $usaha->update([
            'status_buka' => $usaha->status_buka == 'buka' ? 'tutup' : 'buka',
        ]);

        return response()->json(['data' => $usaha->status_buka], Response::HTTP_OK);
    }
}
