<?php

namespace App\Domains\Auth\Controllers;

use App\Domains\Auth\Requests\LoginRequest;
use App\Domains\Auth\Requests\RegisterRequest;
use App\Domains\Cart\Services\CartMergeService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Domains\Auth\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private CartMergeService $mergeService, protected AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_guest' => false
            ]);

            $user->assignRole('Customer');

            $token = $user->createToken('bionic_token')->plainTextToken;

            if ($request->filled('session_token')) {
                $this->mergeService->merge($request->session_token, $user->id);
            }

            return ApiResponse::success([
                'user' => $user,
                'token' => $token
            ], 'User registered successfully', 201);
        } catch (Exception $e) {
            Log::error('Registration Error: ' . $e->getMessage());
            return ApiResponse::error('Registration failed', config('app.debug') ? $e->getMessage() : null, 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->authenticate($request->validated(), $request->ip());
            if (!$result['success']) {
                return ApiResponse::error($result['message'], null, $result['code']);
            }

            if ($request->filled('session_token')) {
                $this->mergeService->merge($request->session_token, $result['data']['user']->id);
            }

            return ApiResponse::success($result['data'], 'Login Successful');
        } catch (Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return ApiResponse::error('Login failed', config('app.debug') ? $e->getMessage() : null, 500);
        }
    }

    public function logout()
    {
        try {
            $user = auth()->user();
            $user->currentAccessToken()->delete();

            return ApiResponse::success(null, 'Logged out successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Logout failed', null, 500);
        }
    }

    public function me()
    {
        return ApiResponse::success(Auth::user());
    }
}
