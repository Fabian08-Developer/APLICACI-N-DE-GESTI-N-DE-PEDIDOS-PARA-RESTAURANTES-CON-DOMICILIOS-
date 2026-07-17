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
use App\Enums\TipoPedido;
use App\Events\PedidoCreado;
use App\Events\PedidoCancelado;
use App\Jobs\DispararNotificacion;
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

            if ($sesion->tipo === TipoPedido::DOMICILIO->value) {
                // Tarifa dinámica: si hay barrio_id usar la pivote sede-barrio
                if ($sesion->barrio_id) {
                    $assignmentService = app(SucursalAssignmentService::class);
                    $tarifa = $assignmentService->obtenerTarifa($sesion->sucursal_id, $sesion->barrio_id);
                    $costoEnvio     = $tarifa['costo_envio'];
                    $tiempoEstimado = $tarifa['tiempo_estimado'];
                } elseif ($sesion->zona_id) {
                    // Fallback legacy: si solo hay zona_id (sesiones antiguas sin barrio_id)
                    $zona = ZonaCobertura::withoutGlobalScopes()->find($sesion->zona_id);
                    if ($zona) {
                        $costoEnvio     = (float) $zona->costo_envio;
                        $tiempoEstimado = (int) $zona->tiempo_estimado;
                    }
                }

                // Fallback de seguridad: si no se aplicó costo (ej. sesión sin barrio_id o zona_id), tomar tarifa activa de la sucursal
                if ($costoEnvio <= 0 && $sesion->sucursal_id) {
                    $tarifaFallback = \App\Models\SucursalBarrioTarifa::where('sucursal_id', $sesion->sucursal_id)
                        ->where('activo', true)
                        ->first();
                    if ($tarifaFallback) {
                        $costoEnvio     = (float) $tarifaFallback->costo_envio;
                        $tiempoEstimado = (int) $tarifaFallback->tiempo_estimado;
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
                'direccion_entrega' => $sesion->tipo === TipoPedido::DOMICILIO->value ? $sesion->direccion_cliente : null,
                'latitud_entrega'   => $sesion->tipo === TipoPedido::DOMICILIO->value ? $sesion->latitud : null,
                'longitud_entrega'  => $sesion->tipo === TipoPedido::DOMICILIO->value ? $sesion->longitud : null,
                'subtotal'          => $subtotal,
                'costo_envio'       => $costoEnvio,
                'total'             => $subtotal + $costoEnvio,
            ]);

            // Auto-asignación inteligente de Mesero para pedidos locales (RF-C36)
            // FIX: reemplaza el N+1 (map + count por cada mesero) con withCount() directo en SQL.
            if ($sesion->tipo === TipoPedido::LOCAL->value) {
                $mesero = User::where('sucursal_id', $sesion->sucursal_id)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'mesero'))
                    ->where('activo', true)
                    ->withCount(['pedidos as pedidos_activos_count' => function ($q) {
                        $q->whereNotIn('estado', [
                            EstadoPedido::ENTREGADO->value,
                            EstadoPedido::CANCELADO->value,
                        ]);
                    }])
                    ->orderBy('pedidos_activos_count', 'asc')
                    ->lockForUpdate() // Prevenir race conditions en la auto-asignación
                    ->first();

                if ($mesero) {
                    $pedido->update(['mesero_id' => $mesero->id]);
                    $sesion->update(['mesero_id' => $mesero->id]);
                }
            }

            // Auto-asignación de Domiciliario para pedidos domicilio
            if ($sesion->tipo === TipoPedido::DOMICILIO->value) {
                $candidatos = PerfilDomiciliario::with(['liquidaciones', 'pedidosActivos'])
                    ->where('sucursal_id', $sesion->sucursal_id)
                    ->where('estado', 'disponible')
                    ->lockForUpdate() // Prevenir asignación simultánea
                    ->get()
                    ->filter(fn ($d) => !$d->tiene_bloqueo);

                if ($candidatos->isNotEmpty()) {
                    $mismaZona  = $candidatos->where('zona_id', $sesion->zona_id);
                    $candidatos = $mismaZona->isNotEmpty() ? $mismaZona : $candidatos;

                    $elegido = $candidatos
                        ->sortBy([
                            ['pedidos_hoy', 'asc'],
                            [fn ($d) => $d->pedidosActivos->count(), 'asc'],
                        ])
                        ->first();

                    if ($elegido) {
                        $pedido->update(['perfil_domiciliario_id' => $elegido->id]);
                    }
                }
            }

            foreach ($items as $item) {
                DetallePedido::create([
                    'pedido_id'          => $pedido->id,
                    'producto_id'        => $item->producto_id,
                    'sucursal_id'        => $item->sucursal_id,
                    'nombre_producto'    => $item->nombre_producto,
                    'precio_unitario'    => $item->precio_unitario,
                    'cantidad'           => $item->cantidad,
                    'subtotal'           => $item->subtotal,
                    'variantes_elegidas' => $item->variantes_elegidas,
                    'adiciones_elegidas' => $item->adiciones_elegidas,
                    'notas'              => $item->notas,
                    'estado'             => 'activo',
                ]);
            }

            HistorialEstadoPedido::create([
                'pedido_id'   => $pedido->id,
                'sucursal_id' => $sesion->sucursal_id,
                'estado'      => EstadoPedido::PENDIENTE_PAGO->value,
            ]);

            $sesion->itemsCarrito()->delete();

            DB::commit();

            // Notificar al equipo de la sucursal que hay un nuevo pedido
            PedidoCreado::dispatch(
                sucursal_id: $pedido->sucursal_id,
                pedido_id:   $pedido->id,
                tipo:        $pedido->tipo,
                short_id:    $pedido->short_id,
            );

            DispararNotificacion::paraSucursal(
                sucursal_id: $pedido->sucursal_id,
                tipo:        'pedido_creado',
                titulo:      '🛒 Nuevo pedido recibido',
                mensaje:     "Pedido #{$pedido->short_id} ({$pedido->tipo}) — $" . number_format($pedido->total, 2),
                datos:       ['pedido_id' => $pedido->id, 'tipo' => $pedido->tipo],
            )->dispatch();

            return $pedido;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancela un pedido iniciado por el cliente.
     *
     * Se usa withoutGlobalScope porque los clientes no tienen sucursal_id en la
     * sesión de Auth (no son staff). Se añade ->where('sucursal_id') explícito
     * para preservar el aislamiento multi-tenant sin contaminar el scope global.
     */
    public function cancelarPedido($sesion, $pedidoId)
    {
        $pedido = Pedido::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('sesion_cliente_id', $sesion->id)
            ->where('sucursal_id', $sesion->sucursal_id) // Doble validación: propiedad + tenant
            ->lockForUpdate() // Lock para prevenir pagos u otros cambios mientras se cancela
            ->find($pedidoId);

        if (!$pedido || !in_array($pedido->estado, [EstadoPedido::PENDIENTE_PAGO->value, EstadoPedido::CREADO->value])) {
            throw new \Exception('No se puede cancelar este pedido.');
        }

        try {
            DB::beginTransaction();

            $pedido->update([
                'estado'             => EstadoPedido::CANCELADO->value,
                'motivo_cancelacion' => 'Cancelado por el cliente.',
            ]);

            HistorialEstadoPedido::create([
                'pedido_id'   => $pedido->id,
                'sucursal_id' => $pedido->sucursal_id,
                'estado'      => EstadoPedido::CANCELADO->value,
            ]);

            $pagoCompletado = $pedido->pagos()
                ->where('estado', EstadoPago::COMPLETADO->value)
                ->first();

            if ($pagoCompletado) {
                $pagoCompletado->update([
                    'estado'         => EstadoPago::REEMBOLSADO->value,
                    'reembolsado_en' => now(),
                ]);
                $pedido->update(['estado_pago' => EstadoPago::REEMBOLSADO->value]);

                $this->enviarEmailReembolso($pedido);
            }

            $this->enviarEmailCancelacion($pedido);

            DB::commit();

            // Notificar al equipo que el cliente canceló
            PedidoCancelado::dispatch(
                sucursal_id: $pedido->sucursal_id,
                pedido_id:   $pedido->id,
                short_id:    $pedido->short_id,
                tipo:        $pedido->tipo,
                motivo:      'Cancelado por el cliente.',
            );

            DispararNotificacion::paraSucursal(
                sucursal_id: $pedido->sucursal_id,
                tipo:        'pedido_cancelado',
                titulo:      "Pedido #{$pedido->short_id} cancelado por cliente",
                mensaje:     'El cliente canceló su propio pedido.',
                datos:       ['pedido_id' => $pedido->id],
            )->dispatch();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cambia el estado de un pedido aplicando todas las reglas de negocio asociadas.
     *
     * Incluye: registro de historial, acreditación de efectivo_pendiente al domiciliario
     * cuando el pedido es entregado y pagado en efectivo.
     *
     * @throws \ValueError si $nuevoEstado no es un valor válido del Enum EstadoPedido.
     */
    public function cambiarEstado(Pedido $pedido, string $nuevoEstado): void
    {
        // Validación de tipo: lanza ValueError automáticamente si el valor no existe en el Enum
        EstadoPedido::from($nuevoEstado);

        $updateData = ['estado' => $nuevoEstado];

        if ($nuevoEstado === EstadoPedido::LISTO->value) {
            $updateData['listo_en'] = now();
        } elseif ($nuevoEstado === EstadoPedido::ENTREGADO->value) {
            $updateData['entregado_en'] = now();
        }

        $pedido->update($updateData);

        // Regla de negocio: acreditar efectivo al domiciliario cuando el pedido es entregado en efectivo
        if (
            $nuevoEstado === EstadoPedido::ENTREGADO->value
            && $pedido->tipo === TipoPedido::DOMICILIO->value
            && $pedido->perfil_domiciliario_id
        ) {
            $domiciliario = PerfilDomiciliario::find($pedido->perfil_domiciliario_id);
            if ($domiciliario) {
                $metodo = strtolower($pedido->metodo_pago ?? 'efectivo');
                if (empty($metodo) || in_array($metodo, ['efectivo', 'cash'])) {
                    $monto = max(0, $pedido->total - $pedido->costo_envio);
                    // increment() es atómico en BD, evita race conditions vs. += + save()
                    $domiciliario->increment('efectivo_pendiente', $monto);
                }
            }
        }

        HistorialEstadoPedido::create([
            'pedido_id'   => $pedido->id,
            'sucursal_id' => $pedido->sucursal_id,
            'estado'      => $nuevoEstado,
            'usuario_id'  => auth()->id(),
            'cambiado_en' => now(),
        ]);
    }

    /**
     * Asigna manualmente un domiciliario a un pedido.
     *
     * Valida el bloqueo por efectivo/liquidaciones pendientes antes de asignar.
     * Actualiza el estado del domiciliario a 'en_ruta' si estaba 'disponible'.
     *
     * @throws \DomainException si el domiciliario tiene bloqueo activo.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException si no se encuentra el domiciliario.
     */
    public function asignarDomiciliario(Pedido $pedido, string $domiciliarioId): PerfilDomiciliario
    {
        $domiciliario = PerfilDomiciliario::findOrFail($domiciliarioId);

        if ($domiciliario->tiene_bloqueo) {
            throw new \DomainException(
                'No se puede asignar: el domiciliario tiene efectivo pendiente por liquidar o superó el límite de efectivo permitido.'
            );
        }

        $pedido->update(['perfil_domiciliario_id' => $domiciliarioId]);

        if ($domiciliario->estado === 'disponible') {
            $domiciliario->update(['estado' => 'en_ruta']);
        }

        HistorialEstadoPedido::create([
            'pedido_id'   => $pedido->id,
            'sucursal_id' => $pedido->sucursal_id,
            'estado'      => $pedido->estado,
            'usuario_id'  => auth()->id(),
            'cambiado_en' => now(),
        ]);

        return $domiciliario;
    }
}
