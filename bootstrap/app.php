<?php

use App\Support\ApiResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Events;
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

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withEvents(function (Events $events): void {
        $events->discover([
            app_path('Listeners'),
            app_path('Domains'),
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // 1. Handle Validation Errors
        $exceptions->render(function (ValidationException $e) {
            return ApiResponse::error(
                'Validation failed',
                $e->errors(),
                422
            );
        });

        // 2. Handle 404 (Not Found)
        $exceptions->render(function (NotFoundHttpException $e) {
            return ApiResponse::error(
                'Resource not found',
                null,
                404
            );
        });

        // 3. Handle Permission/Authorization Errors
        $exceptions->render(function (AuthorizationException $e) {
            return ApiResponse::error(
                'You do not have permission to perform this action',
                null,
                403
            );
        });
    })->create();
