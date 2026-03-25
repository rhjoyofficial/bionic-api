<?php

namespace App\Domains\Auth\Services;

use App\Models\User;
use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthService
{
  public function authenticate(array $data, string $ip)
  {
    $login = strtolower($data['login']);
    $throttlekey = 'login:' . $login . '|' . $ip;

    if (RateLimiter::tooManyAttempts($throttlekey, 5)) {
      $seconds = RateLimiter::availableIn($throttlekey);
      return ApiResponse::error("Too many attempts. Retry in {$seconds} seconds.", null, 429);
    }

    $user = User::where(function ($query) use ($login) {
      $query->where('email', $login)->orWhere('phone', $login);
    })->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {
      RateLimiter::hit($throttlekey, 60); // Increment fail count
      return ApiResponse::error('Invalid credentials', null, 401);
    }

    if (!($user->is_active ?? true)) {
      return ApiResponse::error('Account disabled', null, 403);
    }

    // Success logic
    RateLimiter::clear($throttlekey);

    // Update the last login timestamp
    $user->update([
      'last_login_at' => now(),
    ]);

    $token = $user->createToken('bionic_token')->plainTextToken;

    return [
      'success' => true,
      'data' => [
        'user' => $user,
        'token' => $token,
      ]
    ];
  }
}
