<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Resources\UserResource;
use App\Helpers\ApiResponse;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthService
{
  public function authenticate(array $data, string $ip)
  {
    $login = strtolower($data['login']);

    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
      $login = preg_replace('/[^0-9]/', '', $login);
    }

    $ipKey = 'login:' . $login . '|' . $ip;
    $globalKey = 'login:' . $login;

    if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($globalKey, 20)) {
      $seconds = max(
        RateLimiter::availableIn($ipKey),
        RateLimiter::availableIn($globalKey)
      );

      return [
        'success' => false,
        'message' => "Too many attempts. Retry in {$seconds} seconds.",
        'code' => 429
      ];
    }

    $user = User::where(function ($query) use ($login) {
      $query->where('email', $login)->orWhere('phone', $login);
    })->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {
      RateLimiter::hit($ipKey, 60);    // 1 minute lock for this IP
      RateLimiter::hit($globalKey, 300); // 5 minute lock for the account globally

      return ['success' => false, 'message' => 'Invalid credentials', 'code' => 401];
    }

    if (!($user->is_active ?? true)) {
      return ['success' => false, 'message' => 'Account disabled', 'code' => 403];
    }

    // Success logic
    RateLimiter::clear($ipKey);
    RateLimiter::clear($globalKey);

    // Update the last login timestamp
    $user->update(['last_login_at' => now()]);

    $abilities = $user->hasRole('Admin') ? ['admin:*'] : ['customer:*'];

    $token = $user->createToken('bionic_token', $abilities,  now()->addDays(7))->plainTextToken;

    return [
      'success' => true,
      'data' => [
        'user' => new UserResource($user),
        'token' => $token,
      ]
    ];
  }
}
