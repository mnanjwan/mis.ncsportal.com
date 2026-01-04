<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'onboarding.complete' => \App\Http\Middleware\EnsureOnboardingComplete::class,
            'single.session' => \App\Http\Middleware\EnsureSingleSession::class,
            'session.timeout' => \App\Http\Middleware\CheckSessionTimeout::class,
        ]);
        
        // Add single session middleware to web group after authentication
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureSingleSession::class);
        // Add session timeout middleware to web group to check inactivity
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckSessionTimeout::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
