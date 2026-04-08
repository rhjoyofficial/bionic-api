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

/**
 * WebAuthController — session-based authentication for Blade-rendered pages.
 *
 * Unlike the API AuthController (which is stateless), these routes run under
 * the `web` middleware group, which includes StartSession. Calling Auth::login()
 * here therefore writes to the PHP session and makes @auth work in all subsequent
 * Blade views without relying on a Bearer token.
 *
 * The controller still returns JSON so that AuthManager.js can store the Sanctum
 * token for authorised API calls (e.g. cart, checkout) in the same session.
 */
class WebAuthController extends Controller
{
    public function __construct(
        private CartMergeService $mergeService,
        private AuthService $authService
    ) {}

    // ── Login ─────────────────────────────────────────────────────────────────

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->authenticate($request->validated(), $request->ip());

            if (!$result['success']) {
                return ApiResponse::error($result['message'], null, $result['code']);
            }

            // Auth::login($user) was already called inside AuthService.
            // Because we are on a *web* route, StartSession is active,
            // so the session is persisted → @auth works on next page load.
            $request->session()->regenerate(); // prevent session-fixation attacks

            // Merge guest cart into the newly authenticated user's cart.
            if ($request->filled('session_token')) {
                $this->mergeService->merge($request->session_token, $result['user']->id);
            }

            // Return the new CSRF token (regenerated with the session) so the
            // frontend can update its <meta> tag before the full-page redirect.
            return ApiResponse::success($result['data'], 'Login successful')
                ->header('X-CSRF-TOKEN', csrf_token());
        } catch (Exception $e) {
            Log::error('WebAuthController@login: ' . $e->getMessage());
            return ApiResponse::error('Login failed', config('app.debug') ? $e->getMessage() : null, 500);
        }
    }

    // ── Register ──────────────────────────────────────────────────────────────

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $request->name,
                'phone'    => $request->phone,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'is_guest' => false,
            ]);

            $user->assignRole('Customer');

            $token = $user->createToken('bionic_token', ['customer:*'], now()->addDays(7))->plainTextToken;

            // Merge guest cart before committing the transaction.
            if ($request->filled('session_token')) {
                $this->mergeService->merge($request->session_token, $user->id);
            }

            DB::commit();

            // Establish the PHP session so @auth works on the next Blade render.
            Auth::login($user);
            $request->session()->regenerate();

            return ApiResponse::success([
                'user'  => new UserResource($user),
                'token' => $token,
            ], 'Registration successful', 201)
                ->header('X-CSRF-TOKEN', csrf_token());
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('WebAuthController@register: ' . $e->getMessage());
            return ApiResponse::error('Registration failed', config('app.debug') ? $e->getMessage() : null, 500);
        }
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function logout()
    {
        try {
            $user = Auth::user();

            if ($user) {
                // Revoke all Sanctum tokens so API calls from other devices are also invalidated.
                $user->tokens()->delete();
            }

            // Must target the 'web' guard explicitly.
            // The route uses auth:sanctum middleware, which resolves to Sanctum's
            // RequestGuard — and RequestGuard does NOT implement logout().
            Auth::guard('web')->logout();
            session()->invalidate();
            session()->regenerateToken(); // rotate CSRF token

            return ApiResponse::success(null, 'Logged out successfully');
        } catch (Exception $e) {
            Log::error('WebAuthController@logout: ' . $e->getMessage());
            return ApiResponse::error('Logout failed', null, 500);
        }
    }
}
