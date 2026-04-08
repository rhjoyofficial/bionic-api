<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user holds at least one admin-level role.
 *
 * Any role other than "Customer" is considered admin-level. This avoids
 * hard-coding every role name and automatically picks up new roles added
 * to RoleSeeder in the future.
 */
class EnsureUserIsAdmin
{
    /** Roles that are NOT allowed through the admin gate. */
    private const EXCLUDED_ROLES = ['Customer'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->guest(route('admin.login'));
        }

        // User must have at least one role that is not in the excluded list.
        $hasAdminRole = $user->roles
            ->pluck('name')
            ->diff(self::EXCLUDED_ROLES)
            ->isNotEmpty();

        if (! $hasAdminRole) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            abort(403, 'You do not have access to the admin panel.');
        }

        return $next($request);
    }
}
