<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            abort(401, 'Invalid credentials');
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(200);
    }
}
 