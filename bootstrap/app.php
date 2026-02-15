<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'transactions',
            'transactions/*'
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'audit' => \App\Http\Middleware\AuditLog::class,
            'force.https' => \App\Http\Middleware\ForceHttps::class,
            'restrict.roles' => \App\Http\Middleware\RestrictRoleManagement::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckInactivity::class,
            \App\Http\Middleware\EnsureUnitContext::class,
        ]);

        if (env('APP_ENV') === 'production') {
            $middleware->web(prepend: [
                \App\Http\Middleware\ForceHttps::class,
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
