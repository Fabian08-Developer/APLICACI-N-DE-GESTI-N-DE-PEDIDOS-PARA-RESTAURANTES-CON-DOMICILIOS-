<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $rolesArray = [];
        foreach ($roles as $roleGroup) {
            // Soporte para la sintaxis rol:administrador|gerente
            $rolesArray = array_merge($rolesArray, explode('|', $roleGroup));
        }

        if (!$user->hasRole($rolesArray)) {
            abort(403, 'Acceso denegado. Se requiere uno de los siguientes roles: ' . implode(', ', $rolesArray));
        }

        return $next($request);
    }
}
