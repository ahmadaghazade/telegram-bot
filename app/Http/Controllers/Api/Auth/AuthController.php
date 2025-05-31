<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): UserResource
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return (new UserResource($user))->additional(['token' => $token]);

    }

    public function login(LoginRequest $request): Response|UserResource
    {

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'invlid email or password',
            ], 401);
        }

        $user = Auth::user();
        //            $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return (new UserResource($user))->additional(['token' => $token, 'message' => 'User logged in successfully']);
    }

    public function logout(): \Illuminate\Http\Response
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
