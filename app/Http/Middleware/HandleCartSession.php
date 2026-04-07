<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HandleCartSession
{
    public function handle(Request $request, Closure $next)
    {
        $cookieName = 'bionic_cart_token';

        $token = $request->cookie($cookieName)
            ?? $request->header('X-Session-Token')
            ?? (string) Str::uuid();

        $request->attributes->set('cart_token', $token);

        $response = $next($request);

        // 43200 minutes = 30 days. httpOnly=true prevents XSS reading the token.
        // secure respects the app environment (HTTPS in production).
        $secure = app()->environment('production');
        return $response->withCookie(cookie()->make($cookieName, $token, 43200, '/', null, $secure, true));
    }
}
