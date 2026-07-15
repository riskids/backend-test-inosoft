<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Authenticate a user and return a JWT token.
     *
     * Accepts { email, password } and returns a Bearer token for use
     * on protected (/pickups/*, /payments/*) routes.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('The given data was invalid.', $validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return ApiResponse::error('Invalid email or password.', null, 401);
        }

        return ApiResponse::success([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
        ], 'Login successful');
    }
}