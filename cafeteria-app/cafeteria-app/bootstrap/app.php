<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetActiveTenant::class,
            \App\Http\Middleware\VerificarAutenticacion::class,
        ]);
        $middleware->alias([
            'auth.custom' => \App\Http\Middleware\VerificarAutenticacion::class,
            'cliente.token' => \App\Http\Middleware\ClienteTokenMiddleware::class,
            'rol' => \App\Http\Middleware\CheckRole::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
