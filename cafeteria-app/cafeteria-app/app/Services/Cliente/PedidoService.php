<?php

namespace App\Services\Cliente;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\HistorialEstadoPedido;
use App\Models\ZonaCobertura;
use App\Models\User;
use App\Models\PerfilDomiciliario;
use App\Enums\EstadoPedido;
use App\Enums\EstadoPago;
use App\Services\SucursalAssignmentService;
use Illuminate\Support\Facades\DB;
use App\Traits\OrderEmailNotifications;

class PedidoService
{
    use OrderEmailNotifications;

    public function confirmarPedido($sesion)
    {
        $items = $sesion->itemsCarrito()->with('producto')->get();
        if ($items->isEmpty()) {
            throw new \Exception('El carrito está vacío.');
        }

        try {
            DB::beginTransaction();

            $subtotal = $items->sum('subtotal');
            $costoEnvio = 0.00;
            $tiempoEstimado = 30;

            if ($sesion->tipo === 'domicilio') {
                // Tarifa dinámica: si hay barrio_id usar la pivote sede-barrio
                if ($sesion->barrio_id) {
                    $assignmentService = app(SucursalAssignmentService::class);
                    $tarifa = $assignmentService->obtenerTarifa($sesion->sucursal_id, $sesion->barrio_id);
                    $costoEnvio     = $tarifa['costo_envio'];
                    $tiempoEstimado = $tarifa['tiempo_estimado'];
                } elseif ($sesion->zona_id) {
                    // Fallback legacy: si solo hay zona_id (sesiones antiguas sin barrio_id)
                    $zona = ZonaCobertura::find($sesion->zona_id);
                    if ($zona) {
                        $costoEnvio     = (float) $zona->costo_envio;
                        $tiempoEstimado = (int) $zona->tiempo_estimado;
                    }
                }
            }

            $total = $subtotal + $costoEnvio;

            $pedido = Pedido::create([
                'sucursal_id'       => $sesion->sucursal_id,
                'sesion_cliente_id' => $sesion->id,
                'zona_id'           => $sesion->zona_id,
                'tipo'              => $sesion->tipo,
                'estado'            => EstadoPedido::PENDIENTE_PAGO->value,
                'estado_pago'       => EstadoPago::PENDIENTE->value,
                'direccion_entrega' => $sesion->tipo === 'domicilio' ? $sesion->direccion_cliente : null,
                'latitud_entrega'   => $sesion->tipo === 'domicilio' ? $sesion->latitud : null,
                'longitud_entrega'  => $sesion->tipo === 'domicilio' ? $sesion->longitud : null,
                'subtotal'          => $subtotal,
                'costo_envio'       => $costoEnvio,
                'total'             => $subtotal + $costoEnvio,
            ]);

            // Auto-asignación inteligente de Mesero para pedidos locales (RF-C36)
            if ($sesion->tipo === 'local') {
                $mesero = User::where('sucursal_id', $sesion->sucursal_id)
                    ->where('rol', 'mesero')
                    ->where('activo', true)
                    ->lockForUpdate() // Asegurar no race conditions en la auto asignación
                    ->get()
                    ->map(function ($m) {
                        $m->pedidos_activos_count = Pedido::where('mesero_id', $m->id)
                            ->whereNotIn('estado', ['ENTREGADO', 'CANCELADO'])
                            ->count();
                        return $m;
                    })
                    ->sortBy('pedidos_activos_count')
                    ->first();

                if ($mesero) {
                    $pedido->update(['mesero_id' => $mesero->id]);
                    $sesion->update(['mesero_id' => $mesero->id]);
                }
            }

            // Auto-asignación de Domiciliario para pedidos domicilio
            if ($sesion->tipo === 'domicilio') {
                $candidatos = PerfilDomiciliario::with(['liquidaciones', 'pedidosActivos'])
                    ->where('sucursal_id', $sesion->sucursal_id)
                    ->where('estado', 'disponible')
                    ->lockForUpdate() // Prevenir asignación simultánea
                    ->get()
                    ->filter(fn($d) => !$d->tiene_bloqueo);

                if ($candidatos->isNotEmpty()) {
                    $mismaZona = $candidatos->where('zona_id', $sesion->zona_id);
                    $candidatos = $mismaZona->isNotEmpty() ? $mismaZona : $candidatos;

                    $elegido = $candidatos
                        ->sortBy([
                            ['pedidos_hoy', 'asc'],
                            [fn($d) => $d->pedidosActivos->count(), 'asc'],
                        ])
                        ->first();

                    if ($elegido) {
                        $pedido->update(['perfil_domiciliario_id' => $elegido->id]);
                    }
                }
            }

            foreach ($items as $item) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item->producto_id,
                    'sucursal_id' => $item->sucursal_id,
                    'nombre_producto' => $item->nombre_producto,
                    'precio_unitario' => $item->precio_unitario,
                    'cantidad' => $item->cantidad,
                    'subtotal' => $item->subtotal,
                    'variantes_elegidas' => $item->variantes_elegidas,
                    'adiciones_elegidas' => $item->adiciones_elegidas,
                    'notas' => $item->notas,
                    'estado' => 'activo',
                ]);
            }

            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'sucursal_id' => $sesion->sucursal_id,
                'estado' => EstadoPedido::PENDIENTE_PAGO->value,
            ]);

            $sesion->itemsCarrito()->delete();

            DB::commit();

            return $pedido;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancelarPedido($sesion, $pedidoId)
    {
        $pedido = Pedido::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('sesion_cliente_id', $sesion->id)
            ->lockForUpdate() // Lock para prevenir pagos u otros cambios mientras se cancela
            ->find($pedidoId);

        if (!$pedido || !in_array($pedido->estado, [EstadoPedido::PENDIENTE_PAGO->value, EstadoPedido::CREADO->value])) {
            throw new \Exception('No se puede cancelar este pedido.');
        }

        try {
            DB::beginTransaction();

            $pedido->update([
                'estado' => EstadoPedido::CANCELADO->value,
                'motivo_cancelacion' => 'Cancelado por el cliente.',
            ]);

            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'sucursal_id' => $pedido->sucursal_id,
                'estado' => EstadoPedido::CANCELADO->value,
            ]);

            $pagoCompletado = $pedido->pagos()
                ->where('estado', EstadoPago::COMPLETADO->value)
                ->first();

            if ($pagoCompletado) {
                $pagoCompletado->update([
                    'estado' => EstadoPago::REEMBOLSADO->value,
                    'reembolsado_en' => now(),
                ]);
                $pedido->update(['estado_pago' => EstadoPago::REEMBOLSADO->value]);
                
                $this->enviarEmailReembolso($pedido);
            }

            $this->enviarEmailCancelacion($pedido);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
