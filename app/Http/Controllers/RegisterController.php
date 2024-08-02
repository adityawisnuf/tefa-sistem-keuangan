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
            'role' => 'OrangTua',
            'password' => Hash::make($request->password),
        ]);

        if($user) {
            return response()->json([
                'success' => true,
                'user'    => $user,
            ], 201);
        }

<<<<<<< HEAD


=======
>>>>>>> f5d1416116ad48b215345cc987d5485f23c8550d
        return response()->json([
            'success' => false,
        ], 409);
    }

}
