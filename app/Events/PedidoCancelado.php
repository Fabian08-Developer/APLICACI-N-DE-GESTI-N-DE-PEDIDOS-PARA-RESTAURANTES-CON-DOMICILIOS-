<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento: pedido cancelado.
 * Canal: sucursal.{sucursal_id} — todo el equipo es notificado.
 */
class PedidoCancelado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string  $sucursal_id,
        public readonly string  $pedido_id,
        public readonly string  $short_id,
        public readonly string  $tipo,
        public readonly ?string $motivo = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("sucursal.{$this->sucursal_id}")];
    }

    public function broadcastAs(): string
    {
        return 'pedido.cancelado';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id' => $this->pedido_id,
            'short_id'  => $this->short_id,
            'tipo'      => $this->tipo,
            'motivo'    => $this->motivo,
            'timestamp' => now()->toISOString(),
        ];
    }
}
