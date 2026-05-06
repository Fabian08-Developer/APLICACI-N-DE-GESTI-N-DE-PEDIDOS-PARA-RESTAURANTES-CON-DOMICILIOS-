<?php

namespace App\Http\Middleware;

use App\Models\Pago;
use App\Models\Pedido;
use Closure;
use Illuminate\Http\Request;

/**
 * Verifica que el recurso solicitado (pedido o pago) pertenece
 * a la sesión de mesa activa del request actual.
 *
 * Lee la SesionMesa desde $request->attributes (inyectada por ClienteTokenMiddleware)
 * en lugar de session() — por eso funciona correctamente con múltiples pestañas.
 *
 * Uso en rutas:
 *   ->middleware('cliente.ownership:pedido')
 *   ->middleware('cliente.ownership:pago')
 *
 * Responde 404 en lugar de 403 para no revelar que el recurso existe pero no es del usuario.
 */
class ClienteOwnershipMiddleware
{
    public function handle(Request $request, Closure $next, string $recurso = 'pedido')
    {
        /** @var \App\Models\SesionMesa|null $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');

        if (!$sesionMesa) {
            // ClienteTokenMiddleware debería haber bloqueado antes — defensa extra
            abort(404);
        }

        $valido = match($recurso) {
            'pedido' => $this->verificarPedido($request, $sesionMesa->id),
            'pago'   => $this->verificarPago($request, $sesionMesa->id),
            default  => false,
        };

        if (!$valido) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Recurso no encontrado.'], 404);
            }
            abort(404);
        }

        return $next($request);
    }

    private function verificarPedido(Request $request, int $sesionMesaId): bool
    {
        $pedidoId = $request->route('pedidoId') ?? $request->route('id');

        if (!$pedidoId) {
            return true; // Ruta sin ID específico — no aplica ownership
        }

        return Pedido::where('id', $pedidoId)
            ->where('sesion_mesa_id', $sesionMesaId)
            ->exists();
    }

    private function verificarPago(Request $request, int $sesionMesaId): bool
    {
        $pagoId = $request->route('pagoId');

        if (!$pagoId) {
            return true;
        }

        return Pago::whereHas('pedido', function ($q) use ($sesionMesaId) {
            $q->where('sesion_mesa_id', $sesionMesaId);
        })->where('id', $pagoId)->exists();
    }
}