<?php

use App\Helpers\ApiResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        $middleware->encryptCookies(except: [
            'bionic_cart_token',
        ]);
        $middleware->append(\App\Http\Middleware\SecureHeaders::class);

        // Admin routes are always accessible — even during maintenance mode.
        // This prevents the admin from getting locked out after enabling maintenance.
        $middleware->preventRequestsDuringMaintenance(except: [
            '/admin',
            '/admin/*',
            '/api/v1/admin',
            '/api/v1/admin/*',
            '/up',
        ]);

        $middleware->alias([
            'cart.session' => \App\Http\Middleware\HandleCartSession::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withEvents(discover: [
        app_path('Listeners'),
        app_path('Domains'),
    ])
    ->withExceptions(function (Exceptions $exceptions) {

        /**
         * Determine if the current request expects a JSON response.
         * True for:
         *   - Requests with Accept: application/json header
         *   - Any route under /api/*
         */
        $wantsJson = fn($request) =>
            $request->expectsJson() || $request->is('api/*');

        // ── 404 Not Found ──────────────────────────────────────────────────
        // API  → clean JSON error payload (existing behaviour).
        // Web  → branded 404 Blade page with proper 404 HTTP status.
        //        Laravel auto-discovers resources/views/errors/404.blade.php,
        //        but we register it explicitly here so the context-switch is
        //        crystal-clear and testable.
        $exceptions->render(function (NotFoundHttpException $e, $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error('Resource not found', null, 404);
            }

            return response()->view('errors.404', [], 404);
        });

        // ── Validation Errors (API only) ───────────────────────────────────
        // Web forms use redirect()->back()->withErrors() from the controller,
        // so we only intercept JSON requests here.
        $exceptions->render(function (ValidationException $e, $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error('Validation failed', $e->errors(), 422);
            }
            // Let Laravel's default redirect-with-errors behaviour handle web.
        });

        // ── Authorisation Errors (API only) ────────────────────────────────
        $exceptions->render(function (AuthorizationException $e, $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error(
                    'You do not have permission to perform this action',
                    null,
                    403
                );
            }
            // Web: fall through to Laravel's default 403 handling.
        });
    })->create();
