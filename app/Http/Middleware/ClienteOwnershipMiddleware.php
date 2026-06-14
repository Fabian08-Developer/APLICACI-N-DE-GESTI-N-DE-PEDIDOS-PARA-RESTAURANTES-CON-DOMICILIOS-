<?php

namespace App\Http\Middleware;

use App\Models\Pedido;
use App\Models\Pago;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClienteOwnershipMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $tipo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $tipo): Response
    {
        $sesionMesa = $request->attributes->get('sesion_mesa');
        if (!$sesionMesa) {
            abort(401, 'No hay sesión de cliente activa.');
        }

        if ($tipo === 'pedido') {
            $pedidoId = $request->route('pedidoId');
            if ($pedidoId) {
                $pedido = Pedido::withoutGlobalScope(\App\Scopes\TenantScope::class)->find($pedidoId);
                if (!$pedido || $pedido->sesion_cliente_id !== $sesionMesa->id) {
                    abort(403, 'No tienes permisos para acceder a este pedido.');
                }
            }
        } elseif ($tipo === 'pago') {
            $pagoId = $request->route('pagoId');
            if ($pagoId) {
                $pago = Pago::withoutGlobalScope(\App\Scopes\TenantScope::class)
                    ->with(['pedido' => function($q) {
                        $q->withoutGlobalScope(\App\Scopes\TenantScope::class);
                    }])
                    ->find($pagoId);
                if (!$pago || !$pago->pedido || $pago->pedido->sesion_cliente_id !== $sesionMesa->id) {
                    abort(403, 'No tienes permisos para acceder a este pago.');
                }
            }
        }

        return $next($request);
    }
}
