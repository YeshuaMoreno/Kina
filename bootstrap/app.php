<?php

use App\Http\Middleware\EnsureUserIsNotSuspended;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Bloquea a usuarios suspendidos en cada request web autenticado.
        $middleware->web(append: [
            EnsureUserIsNotSuspended::class,
        ]);

        $middleware->alias([
            'not.suspended' => EnsureUserIsNotSuspended::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
