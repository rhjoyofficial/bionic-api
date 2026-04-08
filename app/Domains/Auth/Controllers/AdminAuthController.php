<?php

namespace App\Domains\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * AdminAuthController — session-based authentication for the Blade admin panel.
 *
 * Separate from WebAuthController (storefront) because:
 *  - Admin login requires a role check before granting access.
 *  - Redirects go to /admin/dashboard, not the storefront.
 *  - No cart merge, no Sanctum token generation — pure session auth.
 */
class AdminAuthController extends Controller
{
    /** Roles that are NOT allowed into the admin panel. */
    private const EXCLUDED_ROLES = ['Customer'];

    // ── Show login form ──────────────────────────────────────────────────

    public function showLoginForm()
    {
        if (Auth::check() && $this->isAdmin(Auth::user())) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    // ── Handle login ─────────────────────────────────────────────────────

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        // Determine whether the user entered an email or phone number.
        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $field     => $request->login,
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), 300); // 5 min decay

            throw ValidationException::withMessages([
                'login' => __('These credentials do not match our records.'),
            ]);
        }

        // Credentials are valid — now verify the user has an admin role.
        $user = Auth::user();

        if (! $this->isAdmin($user)) {
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'login' => __('You do not have permission to access the admin panel.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        // Log activity
        activity('admin-auth')
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip()])
            ->log('Admin logged in');

        return redirect()->intended(route('admin.dashboard'));
    }

    // ── Handle logout ────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            activity('admin-auth')
                ->causedBy($user)
                ->withProperties(['ip' => $request->ip()])
                ->log('Admin logged out');
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function isAdmin($user): bool
    {
        return $user->roles
            ->pluck('name')
            ->diff(self::EXCLUDED_ROLES)
            ->isNotEmpty();
    }

    private function throttleKey(Request $request): string
    {
        return 'admin-login:' . $request->ip();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'login' => __('Too many login attempts. Please try again in :seconds seconds.', [
                'seconds' => $seconds,
            ]),
        ]);
    }
}
