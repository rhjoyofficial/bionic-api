<?php

namespace App\Domains\Auth\Controllers;

use App\Domains\Auth\Requests\LoginRequest;
use App\Domains\Auth\Requests\RegisterRequest;
use App\Domains\Auth\Resources\UserResource;
use App\Domains\Auth\Services\AuthService;
use App\Domains\Cart\Services\CartMergeService;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function __construct(private CartMergeService $mergeService, protected AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        // Start the transaction
        DB::beginTransaction();
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

            // Commit all changes to the database
            DB::commit();
            Auth::login($user);

            return ApiResponse::success([
                'user' => new UserResource($user),
                'token' => $token
            ], 'User registered successfully', 201);
        } catch (Exception $e) {
            // Something went wrong, undo everything
            DB::rollBack();
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
            $user?->currentAccessToken()?->delete();
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return ApiResponse::success(null, 'Logged out successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Logout failed', null, 500);
        }
    }

    public function me()
    {
        return ApiResponse::success(new UserResource(Auth::user()));
    }
}
