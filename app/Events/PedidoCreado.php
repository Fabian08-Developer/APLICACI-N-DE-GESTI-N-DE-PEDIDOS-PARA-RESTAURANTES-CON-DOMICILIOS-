<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento: nuevo pedido creado (local o domicilio).
 * Canal: sucursal.{sucursal_id} — todos los usuarios de la sucursal lo ven.
 */
class PedidoCreado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $sucursal_id,
        public readonly string $pedido_id,
        public readonly string $tipo,        // 'local' | 'domicilio'
        public readonly string $short_id,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("sucursal.{$this->sucursal_id}")];
    }

    public function broadcastAs(): string
    {
        return 'pedido.creado';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id' => $this->pedido_id,
            'short_id'  => $this->short_id,
            'tipo'      => $this->tipo,
            'timestamp' => now()->toISOString(),
        ];
    }
}
