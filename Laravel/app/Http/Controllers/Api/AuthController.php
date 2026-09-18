<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Self-registration of a client: creates the user and its customer profile, returns a token.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:180', 'unique:users,email', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $customer = Customer::create($data);

            return User::create([
                'name' => "{$data['first_name']} {$data['last_name']}",
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_CLIENT,
                'customer_id' => $customer->id,
            ]);
        });

        return response()->json([
            'user' => $user->load('customer:id,first_name,last_name,email'),
            'token' => auth('api')->login($user),
        ], 201);
    }

    /**
     * {"email": "...", "password": "..."} → {"token": "<JWT>"}
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $token = auth('api')->attempt($credentials);
        if (!$token) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return $this->tokenResponse($token);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth('api')->user()->load('customer:id,first_name,last_name,email'));
    }

    /** Puts the current token on the blacklist */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Logged out.']);
    }

    public function refresh(): JsonResponse
    {
        return $this->tokenResponse(auth('api')->refresh());
    }

    private function tokenResponse(string $token): JsonResponse
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
