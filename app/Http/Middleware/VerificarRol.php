<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarRol
{
    /**
     * Uso:
     *   ->middleware('rol:administrador')
     *   ->middleware('rol:administrador,mesero')   ← OR: cualquiera de los dos puede pasar
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $usuario = Auth::user();

        if (!$usuario->relationLoaded('rol')) {
            $usuario->load('rol');
        }

        $rolUsuario = $usuario->rol->nombre;

        // El administrador siempre tiene acceso a todo
        if ($rolUsuario === 'administrador') {
            return $next($request);
        }

        if (!in_array($rolUsuario, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error'   => 'Acceso denegado.',
                    'mensaje' => 'No tienes permiso para realizar esta acción.',
                ], 403);
            }

            // No revelar la existencia de la ruta — abort 403 genérico
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}