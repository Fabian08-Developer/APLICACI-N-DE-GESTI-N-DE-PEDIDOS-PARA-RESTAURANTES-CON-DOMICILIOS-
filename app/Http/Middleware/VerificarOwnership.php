<?php

namespace App\Http\Middleware;

use App\Models\Pedido;
use App\Models\SesionMesa;
use Closure;
use Illuminate\Http\Request;

class VerificarOwnership
{
    /**
     * Verifica que el recurso solicitado pertenece a la sesión de mesa activa.
     *
     * Uso en rutas de cliente:
     *   ->middleware('cliente.ownership:pedido')
     *   ->middleware('cliente.ownership:pago')
     *
     * El parámetro indica qué tipo de recurso verificar.
     * El ID del recurso se lee del parámetro de ruta {pedidoId} o {pagoId}.
     */
    public function handle(Request $request, Closure $next, string $recurso = 'pedido')
    {
        $sesionMesaId = session('sesion_mesa_id');

        if (!$sesionMesaId) {
            return $this->accesoDenegado($request);
        }

        $permiso = match($recurso) {
            'pedido' => $this->verificarPedido($request, $sesionMesaId),
            'pago'   => $this->verificarPago($request, $sesionMesaId),
            default  => false,
        };

        if (!$permiso) {
            return $this->accesoDenegado($request);
        }

        return $next($request);
    }

    /**
     * Verifica que el pedido solicitado pertenece a la sesión de mesa activa.
     * Usa findOrFail internamente para que si el pedido no existe, retorne 404.
     */
    private function verificarPedido(Request $request, int $sesionMesaId): bool
    {
        $pedidoId = $request->route('pedidoId') ?? $request->route('id');

        if (!$pedidoId) {
            return true; // Ruta sin ID específico (ej: /pedido/confirmar) — no aplica ownership
        }

        $pedido = Pedido::find($pedidoId);

        // Si el pedido no existe, devolvemos false → 404 genérico
        // No revelamos si el ID existe pero no es del usuario
        if (!$pedido) {
            return false;
        }

        return $pedido->sesion_mesa_id === $sesionMesaId;
    }

    /**
     * Verifica que el pago pertenece a un pedido de la sesión de mesa activa.
     */
    private function verificarPago(Request $request, int $sesionMesaId): bool
    {
        $pagoId = $request->route('pagoId');

        if (!$pagoId) {
            return true;
        }

        // Buscar el pago y verificar que su pedido pertenece a esta sesión
        $pago = \App\Models\Pago::with('pedido')->find($pagoId);

        if (!$pago || !$pago->pedido) {
            return false;
        }

        return $pago->pedido->sesion_mesa_id === $sesionMesaId;
    }

    private function accesoDenegado(Request $request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Recurso no encontrado.'], 404);
        }

        // Usamos 404, no 403 — no revelar que el recurso existe pero no es suyo
        abort(404);
    }
}