<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento: pedido de domicilio asignado a un domiciliario.
 * Canales:
 *   - sucursal.{sucursal_id}   → el equipo admin ve la asignación
 *   - domiciliario.{user_id}   → el domiciliario recibe su alerta personal
 */
class PedidoAsignadoDomiciliario implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string  $sucursal_id,
        public readonly string  $pedido_id,
        public readonly string  $short_id,
        public readonly string  $domiciliario_nombre,
        public readonly ?string $domiciliario_user_id = null, // Para canal personal
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel("sucursal.{$this->sucursal_id}")];

        // Canal personal del domiciliario si tenemos su user_id
        if ($this->domiciliario_user_id) {
            $channels[] = new PrivateChannel("domiciliario.{$this->domiciliario_user_id}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'pedido.asignado';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id'             => $this->pedido_id,
            'short_id'              => $this->short_id,
            'domiciliario_nombre'   => $this->domiciliario_nombre,
            'timestamp'             => now()->toISOString(),
        ];
    }
}
