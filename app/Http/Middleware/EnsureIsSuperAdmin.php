<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\RolUsuario;

class EnsureIsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->hasRole(RolUsuario::SUPER_ADMIN->value)) {
            abort(403, 'No tienes permisos para acceder a esta sección global.');
        }

        return $next($request);
    }
}


