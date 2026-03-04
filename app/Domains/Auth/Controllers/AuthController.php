<?php

namespace App\Domains\Auth\Controllers;

use App\Domains\Auth\Requests\LoginRequest;
use App\Domains\Auth\Requests\RegisterRequest;
use App\Domains\Cart\Services\CartMergeService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function __construct(private CartMergeService $mergeService) {}

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
            $key = 'login:' . strtolower($request->input('login')) . '|' . $request->ip();

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                return ApiResponse::error("Too many attempts. Retry in {$seconds} seconds.", null, 429);
            }

            $login = $request->input('login');
            $user = User::where('email', $login)->orWhere('phone', $login)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                RateLimiter::hit($key, 60); // Increment fail count
                return ApiResponse::error('Invalid credentials', null, 401);
            }

            if (!($user->is_active ?? true)) {
                return ApiResponse::error('Account disabled', null, 403);
            }

            // Success logic
            RateLimiter::clear($key);
            $token = $user->createToken('bionic_token')->plainTextToken;

            if ($request->filled('session_token')) {
                $this->mergeService->merge($request->session_token, $user->id);
            }

            return ApiResponse::success([
                'user' => $user,
                'token' => $token
            ], 'Login successful');
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
