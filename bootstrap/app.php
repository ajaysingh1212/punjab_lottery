<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/auth.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active.user' => \App\Http\Middleware\ActiveUser::class,
            'ip.security' => \App\Http\Middleware\BlockBlockedIp::class,
            'trusted.device' => \App\Http\Middleware\RequireTrustedDevice::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
