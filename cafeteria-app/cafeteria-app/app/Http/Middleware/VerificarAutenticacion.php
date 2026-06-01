<?php

namespace App\Http\Middleware;

use App\Models\Sesion;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Autenticación basada en TOKEN POR PESTAÑA.
 *
 * ANTES: usaba Auth::check() (sesión PHP compartida entre pestañas)
 * AHORA: lee el token desde el request (header, query, body) y autentica
 *        solo para este request con Auth::onceUsingId().
 *
 * Esto permite que cada pestaña del navegador tenga un usuario diferente:
 *   - Pestaña 1: admin (token A en sessionStorage)
 *   - Pestaña 2: mesero (token B en sessionStorage)
 *   - Pestaña 3: cocina (token C en sessionStorage)
 *
 * El token se inyecta automáticamente por el JS de staff-token.blade.php.
 */
class VerificarAutenticacion
{
    public function handle(Request $request, Closure $next)
    {
        // Leer token desde: header (AJAX) → query param (links) → form field (POST) → post-login init
        $token = $request->header('X-Staff-Token')
              ?? $request->query('_st')
              ?? $request->input('_st')
              ?? $request->query('_token_init');

        // 1. Si NO hay token de staff, verificamos si es un super-admin o gerente (sesión tradicional)
        if (!$token && Auth::check() && Auth::user()->hasRole(['super-admin', 'gerente'])) {
            $user = Auth::user();
            if ($user && ($user->ultimo_acceso_en === null || Carbon::parse($user->ultimo_acceso_en)->diffInMinutes(now()) >= 5)) {
                $user->ultimo_acceso_en = now();
                $user->save();
            }
            return $next($request);
        }

        if (!$token) {
            // Verificar si la ruta actual requiere autenticación custom
            $route = $request->route();
            $requiresCustomAuth = $route && (
                in_array('auth.custom', $route->gatherMiddleware()) || 
                in_array(self::class, $route->gatherMiddleware())
            );

            if (!$requiresCustomAuth) {
                return $next($request);
            }

            // Para AJAX: error directo
            if ($request->expectsJson() || $request->is('livewire/*')) {
                return response()->json(['error' => 'No autenticado.'], 401);
            }

            // Para navegador: mostrar página puente que revisa sessionStorage
            // Si sessionStorage tiene el token, redirige a la misma URL con ?_st=TOKEN
            // Si no tiene token, redirige al login
            return response()->view('partials.token-bridge');
        }

        // Validar token contra la tabla sesiones
        // Se desactiva el TenantScope porque no estamos autenticados aún (1=0 de lo contrario)
        $sesion = Sesion::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('token', $token)
            ->where('activa', true)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$sesion) {
            return $this->noAutenticado($request, 'Tu sesión expiró o fue cerrada. Por favor inicia sesión nuevamente.');
        }

        // ✅ IMPORTANTÍSIMO: Configurar el TenantScope con la sucursal de esta sesión
        // ANTES de ejecutar Auth::onceUsingId(). De lo contrario, Auth::onceUsingId()
        // consultará el usuario pero el TenantScope lo bloqueará con `1=0`.
        \App\Scopes\TenantScope::setTenantId($sesion->sucursal_id);

        // ✅ Autenticar SOLO PARA ESTE REQUEST — no toca la sesión PHP compartida
        // Auth::onceUsingId() no escribe en session() → cada pestaña es independiente
        if (!Auth::onceUsingId($sesion->usuario_id)) {
            // Usuario no existe en BD (fue eliminado) o bloqueado por scope
            $sesion->update(['activa' => false]);
            return $this->noAutenticado($request, 'Tu cuenta fue desactivada.');
        }

        // Verificar que el usuario sigue activo
        if (!Auth::user()->estado) {
            $sesion->update(['activa' => false]);
            return $this->noAutenticado($request, 'Tu cuenta está desactivada, contacta al administrador.');
        }

        // Registrar/actualizar último acceso
        $user = Auth::user();
        if ($user && ($user->ultimo_acceso_en === null || Carbon::parse($user->ultimo_acceso_en)->diffInMinutes(now()) >= 5)) {
            $user->ultimo_acceso_en = now();
            $user->save();
        }

        // Renovar si está próxima a expirar (menos de 1 hora)
        if (Carbon::parse($sesion->fecha_expiracion)->diffInMinutes(now()) < 60) {
            $sesion->update(['fecha_expiracion' => now()->addDays(7)]);
        }

        // Ejecutar el controller
        $response = $next($request);

        // ✅ Si el controller devuelve un redirect, agregar _st automáticamente
        // Esto evita la pantalla blanca del token-bridge en redirect()->route() o back()
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $targetUrl = $response->getTargetUrl();
            if (!str_contains($targetUrl, '_st=')) {
                $separator = str_contains($targetUrl, '?') ? '&' : '?';
                $response->setTargetUrl($targetUrl . $separator . '_st=' . $token);
            }
        }

        return $response;
    }

    private function noAutenticado(Request $request, string $mensaje): mixed
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $mensaje], 401);
        }

        return redirect()->route('login')
            ->with('error', $mensaje);
    }
}