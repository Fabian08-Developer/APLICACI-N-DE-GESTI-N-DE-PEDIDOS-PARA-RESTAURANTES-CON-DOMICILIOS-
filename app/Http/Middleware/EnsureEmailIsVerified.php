<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->hasVerifiedEmail()) {
            // Si no está verificado, lo redirigimos a una vista de aviso
            return redirect()->route('verification.notice')
                ->with('warning', 'Debes verificar tu correo para acceder a las funciones operativas.');
        }

        return $next($request);
    }
}
