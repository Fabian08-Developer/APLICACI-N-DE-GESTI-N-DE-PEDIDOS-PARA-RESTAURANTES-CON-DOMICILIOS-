<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento: cambio de estado en un pedido.
 * Canal: sucursal.{sucursal_id} — todo el equipo ve los cambios de estado.
 */
class PedidoCambioEstado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $sucursal_id,
        public readonly string $pedido_id,
        public readonly string $short_id,
        public readonly string $estado_anterior,
        public readonly string $estado_nuevo,
        public readonly string $tipo_pedido,   // 'local' | 'domicilio'
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("sucursal.{$this->sucursal_id}")];
    }

    public function broadcastAs(): string
    {
        return 'pedido.estado_cambiado';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id'       => $this->pedido_id,
            'short_id'        => $this->short_id,
            'estado_anterior' => $this->estado_anterior,
            'estado_nuevo'    => $this->estado_nuevo,
            'tipo'            => $this->tipo_pedido,
            'timestamp'       => now()->toISOString(),
        ];
    }
}
