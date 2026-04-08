<?php

namespace App\Domains\Auth\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Send a password-reset link to the user's email.
     * Throttled at 3 req/min in routes/public.php.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return ApiResponse::success(null, __($status));
        }

        // INVALID_USER returns the same vague message to prevent user enumeration
        if ($status === Password::INVALID_USER) {
            // Deliberate: do NOT reveal whether the email exists
            return ApiResponse::success(null, 'If that email is registered, a reset link has been sent.');
        }

        // RESET_THROTTLED
        return ApiResponse::error(__($status), null, 429);
    }

    /**
     * Validate the reset token and update the password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all existing Sanctum tokens for security
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return ApiResponse::success(null, __($status));
        }

        return ApiResponse::error(__($status), null, 422);
    }
}
