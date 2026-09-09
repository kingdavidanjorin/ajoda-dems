<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureUserIsCustomer;
use App\Http\Middleware\EnsureUserIsStaff;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
        |--------------------------------------------------------------------------
        | Trusted Hosts
        |--------------------------------------------------------------------------
        |
        | Laravel will read the host from config('app.url') when processing
        | HTTP requests, after the configuration service has been loaded.
        | Calling config() directly in this bootstrap callback is too early.
        |
        */

        $middleware->trustHosts();

        /*
        |--------------------------------------------------------------------------
        | Custom Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'active' => EnsureAccountIsActive::class,
            'customer' => EnsureUserIsCustomer::class,
            'staff' => EnsureUserIsStaff::class,

            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request): bool =>
            $request->is('api/*')
                || $request->expectsJson(),
        );
    })
    ->create();
