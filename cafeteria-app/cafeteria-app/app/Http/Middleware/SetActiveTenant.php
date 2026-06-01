<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Si el usuario es Super Admin, limpiamos cualquier tenant previo de la sesión
            // para que pueda ver todo el sistema sin filtros.
            if ($user->hasRole('super-admin')) {
                session()->forget('active_tenant_id');
                return $next($request);
            }

            // Si el usuario pertenece a una sucursal, la marcamos como activa
            if ($user->sucursal_id) {
                session(['active_tenant_id' => $user->sucursal_id]);
            }
        }

        return $next($request);
    }
}


