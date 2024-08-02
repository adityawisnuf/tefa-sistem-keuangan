<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'Orang Tua',
            'password' => Hash::make($request->password),
        ]);

        if($user) {
            return response()->json([
                'success' => true,
                'user'    => $user,
            ], 201);
        }



        return response()->json([
            'success' => false,
        ], 409);
    }

}
