<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveBranch
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
            
            // Si el usuario pertenece a una sucursal, la marcamos como activa en la sesión (Redis)
            if ($user->sucursal_id) {
                session(['active_branch_id' => $user->sucursal_id]);
            }
        }

        return $next($request);
    }
}
