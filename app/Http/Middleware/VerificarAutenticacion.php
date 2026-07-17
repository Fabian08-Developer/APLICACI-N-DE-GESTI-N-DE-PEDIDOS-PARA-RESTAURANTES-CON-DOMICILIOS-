<?php

namespace App\Http\Middleware;

use App\Models\Sesion;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarAutenticacion
{
    public function handle(Request $request, Closure $next)
    {
        // ─── PRIORIDAD MÁXIMA: Super-admin y Gerente usan sesión PHP tradicional ─────────────
        // Debe verificarse ANTES de leer el staff_token para evitar que un token residual
        // de una sesión staff anterior suplante su identidad vía Auth::onceUsingId().
        if (Auth::check() && Auth::user()->hasRole(['super-admin', 'gerente'])) {
            $user = Auth::user();

            // Limpiar cualquier staff_token residual en sesión Y cookie del navegador
            // para evitar que future requests lo lean y suplanten la identidad.
            session()->forget('staff_token');

            return $next($request)->withCookie(
                \Illuminate\Support\Facades\Cookie::forget('staff_token')
            );
        }

        // Leer token desde cookie HTTP-only de forma segura, o header X-Staff-Token para apps
        // (solo para usuarios staff: administrador, mesero, cocina, domiciliario)
        $token = $request->cookie('staff_token')
              ?? $request->header('X-Staff-Token')
              ?? session('staff_token');

        if (!$token) {
            $route = $request->route();
            $requiresCustomAuth = $route && (
                in_array('auth.custom', $route->gatherMiddleware()) || 
                in_array(self::class, $route->gatherMiddleware())
            );

            if (!$requiresCustomAuth) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('livewire/*')) {
                return response()->json(['error' => 'No autenticado.'], 401);
            }

            return redirect()->route('login')->with('error', 'Por favor inicia sesión.');
        }

        $sesion = Sesion::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('token', $token)
            ->where('activa', true)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$sesion) {
            return $this->noAutenticado($request, 'Tu sesión expiró o fue cerrada. Por favor inicia sesión nuevamente.');
        }

        \App\Scopes\TenantScope::setTenantId($sesion->sucursal_id);

        if (!Auth::onceUsingId($sesion->usuario_id)) {
            $sesion->update(['activa' => false]);
            return $this->noAutenticado($request, 'Tu cuenta fue desactivada.');
        }

        if (!Auth::user()->estado) {
            $sesion->update(['activa' => false]);
            return $this->noAutenticado($request, 'Tu cuenta está desactivada, contacta al administrador.');
        }

        $user = Auth::user();
        if ($user && ($user->ultimo_acceso_en === null || Carbon::parse($user->ultimo_acceso_en)->diffInMinutes(now()) >= 5)) {
            $user->ultimo_acceso_en = now();
            $user->save();
        }

        if (Carbon::parse($sesion->fecha_expiracion)->diffInMinutes(now()) < 60) {
            $sesion->update(['fecha_expiracion' => now()->addDays(7)]);
        }

        $response = $next($request);

        return $response;
    }

    private function noAutenticado(Request $request, string $mensaje): mixed
    {
        // Limpiamos la cookie si el token era inválido
        $cookie = \Illuminate\Support\Facades\Cookie::forget('staff_token');
        session()->forget('staff_token');

        if ($request->expectsJson()) {
            return response()->json(['error' => $mensaje], 401)->withCookie($cookie);
        }

        return redirect()->route('login')
            ->with('error', $mensaje)
            ->withCookie($cookie);
    }
}