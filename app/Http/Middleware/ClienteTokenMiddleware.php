<?php

namespace App\Http\Middleware;

use App\Models\Mesa;
use App\Models\SesionMesa;
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
    const TIMEOUT_MINUTOS = 15;

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

        // Validar el token contra la BD y cargar la mesa de una vez
        $sesionMesa = SesionMesa::with('mesa')
            ->where('token', $token)
            ->where('estado', SesionMesa::ESTADO_ACTIVA)
            ->first();

        if (!$sesionMesa) {
            return $this->sinAcceso(
                $request,
                'Tu sesión no es válida o expiró. Escanea el QR nuevamente.'
            );
        }

        // Verificar que la mesa sigue ocupada (no fue liberada por el mesero)
        if (!$sesionMesa->mesa || $sesionMesa->mesa->estado === 'DISPONIBLE') {
            return $this->sinAcceso(
                $request,
                'Esta sesión de mesa fue cerrada. Escanea el QR para comenzar.'
            );
        }

        // Verificar inactividad usando updated_at de SesionMesa
        // (no necesitamos session('ultima_actividad') — la BD es la fuente de verdad)
        if ($sesionMesa->updated_at->diffInMinutes(now()) >= self::TIMEOUT_MINUTOS) {
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

        // Actualizar actividad en BD (no en session)
        // touch() actualiza updated_at → sirve como marca de última actividad
        $sesionMesa->touch();

        return $next($request);
    }

    private function cerrarPorInactividad(SesionMesa $sesionMesa): void
    {
        // cerrar() ya valida si hay otras sesiones activas antes de liberar la mesa
        $sesionMesa->cerrar(SesionMesa::MOTIVO_INACTIVIDAD);
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