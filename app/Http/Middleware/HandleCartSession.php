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

        // 43200 minutes = 30 days.
        // httpOnly=false: the cart token is NOT sensitive auth data — JS must be able
        //   to read it so CartManager can send it as X-Session-Token during auth.
        // secure: HTTPS-only in production.
        $secure = app()->environment('production');
        return $response->withCookie(cookie()->make($cookieName, $token, 43200, '/', null, $secure, false));
    }
}
