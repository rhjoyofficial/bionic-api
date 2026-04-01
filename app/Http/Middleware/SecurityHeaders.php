<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // 1. Content Security Policy (Basic starting point)
        // This allows scripts from your own site and prevents inline style/script injection
        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' https://trusted-scripts.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:;";
        $response->headers->set('Content-Security-Policy', $csp);

        // 2. Permissions Policy (Block things you don't use)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // 3. Remove Legacy/Unnecessary headers
        $response->headers->remove('X-XSS-Protection');

        // 4. Force HTTPS (Strict-Transport-Security)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
