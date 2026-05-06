<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            // ── Staff autenticado ───────────────────────────────────────────
            'auth.custom'       => \App\Http\Middleware\VerificarAutenticacion::class,
            'rol'               => \App\Http\Middleware\VerificarRol::class,
            'guest.only'        => \App\Http\Middleware\GuestOnly::class,

            // ── Cliente QR — token en URL (reemplaza cliente.sesion) ────────
            // ClienteSessionMiddleware ya NO se usa — eliminado del alias
            'cliente.token'     => \App\Http\Middleware\ClienteTokenMiddleware::class,
            'cliente.ownership' => \App\Http\Middleware\ClienteOwnershipMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Acceso denegado.'], 403);
            }
            return response()->view('errors.403', [], 403);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Recurso no encontrado.'], 404);
            }
            return response()->view('errors.404', [], 404);
        });
    })->withSchedule(function ($schedule) {
        $schedule->command('reports:send-scheduled')->everyMinute();
    })->create();