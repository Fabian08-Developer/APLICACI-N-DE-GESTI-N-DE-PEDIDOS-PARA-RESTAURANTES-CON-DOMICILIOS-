<?php

namespace App\Http\Middleware;

use App\Models\Mesa;
use App\Models\SesionMesa;
use Closure;
use Illuminate\Http\Request;

class ClienteSessionMiddleware
{
    const TIMEOUT_MINUTOS = 15;

    public function handle(Request $request, Closure $next)
    {
        // Solo aplica a rutas del cliente
        if (!$request->is('cliente/*')) {
            return $next($request);
        }

        // Rutas que no requieren sesión activa
        if ($this->esRutaPublicaCliente($request)) {
            return $next($request);
        }

        // ✅ CORRECCIÓN: intentar restaurar sesión desde token en URL
        // Útil cuando el cliente recarga la página o sigue un enlace directo
        if (!session()->has('sesion_mesa_id')) {
            $this->intentarRestaurarSesion($request);
        }

        // Si después del intento de restauración no hay sesión, rechazar
        if (!session()->has('sesion_mesa_id')) {
            return redirect('/')->with('error', 'Escanea el QR para acceder al menú.');
        }

        // Verificar que la sesión de mesa sigue activa en la BD
        // (puede haber sido cerrada por el mesero o el admin)
        $sesionMesa = SesionMesa::find(session('sesion_mesa_id'));

        if (!$sesionMesa || $sesionMesa->estado !== SesionMesa::ESTADO_ACTIVA) {
            $this->limpiarSesionCliente();
            return redirect('/')->with('error', 'Tu sesión de mesa fue cerrada. Escanea el QR nuevamente.');
        }

        // Verificar inactividad
        if ($this->verificarInactividad($sesionMesa)) {
            return redirect('/')->with('error', 'Tu sesión expiró por inactividad. Escanea el QR nuevamente.');
        }

        // Renovar timestamp de actividad
        session(['ultima_actividad' => now()->toDateTimeString()]);

        return $next($request);
    }

    /**
     * Rutas de cliente que no requieren sesión activa
     */
    private function esRutaPublicaCliente(Request $request): bool
    {
        return $request->is('cliente/sesion/*')
            || $request->is('cliente/sin-sesion')
            || $request->is('cliente/salir');
    }

    /**
     * ✅ CORRECCIÓN: busca directamente en sesiones_mesa por token
     * Antes buscaba en SubSesion (modelo inexistente) y usaba sesion_mesa_id
     * como si SesionMesa tuviera una FK hacia sí misma.
     */
    private function intentarRestaurarSesion(Request $request): void
    {
        $token = $request->query('t') ?? $request->input('_t');

        if (!$token) {
            return;
        }

        // ✅ Buscar directamente en sesiones_mesa — es la única tabla de sesiones de cliente
        $sesionMesa = SesionMesa::where('token', $token)  // ← token está en sesiones_mesa
            ->where('estado', SesionMesa::ESTADO_ACTIVA)
            ->first();

        if (!$sesionMesa) {
            return;
        }

        // ✅ Guardar en sesión PHP solo los IDs — nunca datos denormalizados
        session([
            'sesion_mesa_id'   => $sesionMesa->id,
            'mesa_id'          => $sesionMesa->mesa_id,
            'tipo_sesion'      => $sesionMesa->tipo_sesion ?? 'INDIVIDUAL',
            'codigo_grupo'     => $sesionMesa->codigo_grupo,
            'ultima_actividad' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Verifica inactividad y cierra la sesión de mesa si corresponde.
     * Retorna true si hubo timeout (el caller debe redirigir).
     */
    private function verificarInactividad(SesionMesa $sesionMesa): bool
    {
        $ultimaActividad = session('ultima_actividad');

        if (!$ultimaActividad) {
            return false;
        }

        if (now()->diffInMinutes($ultimaActividad) < self::TIMEOUT_MINUTOS) {
            return false;
        }

        // ✅ Cerrar la sesión de mesa correctamente usando el método del modelo
        $sesionMesa->cerrar(SesionMesa::MOTIVO_INACTIVIDAD);

        // Liberar la mesa
        Mesa::where('id', $sesionMesa->mesa_id)
            ->update(['estado' => 'DISPONIBLE']);

        $this->limpiarSesionCliente();

        return true;
    }

    /**
     * Limpia todos los datos de cliente de la sesión PHP.
     * ✅ CORRECCIÓN: eliminado el bloque de Usuario con email temp_
     * que podría afectar usuarios de otras sesiones por una race condition.
     */
    public static function limpiarSesionCliente(): void
    {
        session()->forget([
            'sesion_mesa_id',
            'mesa_id',
            'tipo_sesion',
            'codigo_grupo',
            'es_lider',
            'carrito',
            'pedido_id',
            'qr_codigo',
            'ultima_actividad',
        ]);
    }
}