<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //if auth failed
        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password.'
            ], 401);
        }

        return response()->json([
            'message'   => 'success',
            'token'     => $token
        ]);
    }

    public function getAllDatas($id)
    {
        return User::find($id);
    }
}
