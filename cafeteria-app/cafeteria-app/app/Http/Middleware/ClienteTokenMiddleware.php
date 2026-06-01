<?php

namespace App\Http\Middleware;

use App\Models\Mesa;
use App\Models\SesionCliente;
use Closure;
use Illuminate\Http\Request;

/**
 * REEMPLAZA a ClienteSessionMiddleware.
 *
 * Diferencia arquitectónica fundamental:
 * - Antes: leía sesion_mesa_id desde session() → compartida por todas las pestañas
 * - Ahora: lee el token desde la URL (?t=TOKEN) → cada pestaña tiene su propia URL
 *
 * Esto resuelve el problema de pestañas múltiples porque la cookie de sesión
 * del navegador es por dominio (compartida), pero la URL es por pestaña (independiente).
 */
class ClienteTokenMiddleware
{
    const TIMEOUT_MINUTOS = 10;

    public function handle(Request $request, Closure $next)
    {
        // Leer token desde la URL (GET) o desde el body (POST con campo oculto _t)
        $token = $request->query('t') ?? $request->input('_t');

        if (!$token) {
            return $this->sinAcceso(
                $request,
                'Escanea el código QR de tu mesa para acceder al menú.'
            );
        }

        // Validar el token contra la BD y cargar la mesa de una vez.
        // NOTA: Se desactiva el TenantScope aquí porque al no haber sesión ni autenticación aún,
        // el Scope devolvería 1=0. El token es globalmente único.
        $sesionMesa = SesionCliente::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->with(['mesa' => function($q) {
                $q->withoutGlobalScope(\App\Scopes\TenantScope::class);
            }])
            ->where('token', $token)
            ->where('activo', true)
            ->first();

        if (!$sesionMesa) {
            return $this->sinAcceso(
                $request,
                'Tu sesión no es válida o expiró. Escanea el QR nuevamente.'
            );
        }

        // Verificar que la mesa sigue ocupada (no fue liberada por el mesero) - Solo para sesiones locales
        if ($sesionMesa->tipo === 'local' && (!$sesionMesa->mesa || $sesionMesa->mesa->estado === Mesa::ESTADO_DISPONIBLE)) {
            return $this->sinAcceso(
                $request,
                'Esta sesión de mesa fue cerrada. Escanea el QR para comenzar.'
            );
        }

        // Verificar inactividad usando actualizado_en de SesionMesa
        // (no necesitamos session('ultima_actividad') — la BD es la fuente de verdad)
        if ($sesionMesa->actualizado_en->diffInMinutes(now()) >= self::TIMEOUT_MINUTOS) {
            $this->cerrarPorInactividad($sesionMesa);

            return $this->sinAcceso(
                $request,
                'Tu sesión expiró por inactividad. Escanea el QR nuevamente.'
            );
        }

        // ✅ Adjuntar al request — disponible en el controlador sin tocar session()
        // $request->attributes->get('sesion_mesa')  → objeto SesionMesa
        // $request->attributes->get('token_mesa')   → string del token
        $request->attributes->set('sesion_mesa', $sesionMesa);
        $request->attributes->set('token_mesa', $token);

        // ✅ Activar el TenantScope globalmente para todo este request
        // Evitamos así hacer session(['sucursal_id' => ...]) que cruzaba pestañas
        \App\Scopes\TenantScope::setTenantId($sesionMesa->sucursal_id);

        // Actualizar actividad en BD (no en session)
        // touch() actualiza updated_at → sirve como marca de última actividad
        $sesionMesa->touch();

        return $next($request);
    }

    private function cerrarPorInactividad(SesionCliente $sesionMesa): void
    {
        // cerrar() ya valida si hay otras sesiones activas antes de liberar la mesa
        $sesionMesa->cerrar();
    }

    private function sinAcceso(Request $request, string $mensaje): mixed
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error'     => $mensaje,
                'redirigir' => route('cliente.sin-sesion'),
            ], 401);
        }

        return redirect()->route('cliente.sin-sesion')->with('error', $mensaje);
    }
}