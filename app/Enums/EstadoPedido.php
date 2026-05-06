<?php

namespace App\Enums;

enum EstadoPedido: string
{
    case PENDIENTE_PAGO = 'PENDIENTE_PAGO';
    case CREADO        = 'CREADO';
    case EN_COCINA     = 'EN_COCINA';
    case EN_PREPARACION = 'EN_PREPARACION';
    case LISTO         = 'LISTO';
    case ENTREGADO     = 'ENTREGADO';
    case CANCELADO     = 'CANCELADO';

    /**
     * Orden numérico del estado para comparaciones de progreso.
     * Un estado con orden mayor significa que el pedido avanzó.
     */
    public function orden(): int
    {
        return match($this) {
            self::PENDIENTE_PAGO => -2,
            self::CREADO         => 0,
            self::EN_COCINA      => 1,
            self::EN_PREPARACION => 2,
            self::LISTO          => 3,
            self::ENTREGADO      => 4,
            self::CANCELADO      => -1,
        };
    }

    /**
     * Retorna true si el pedido ya alcanzó o superó el estado dado.
     */
    public function alcanzó(self $estado): bool
    {
        return $this->orden() >= $estado->orden();
    }

    /**
     * Retorna true si el pedido está en un estado final.
     */
    public function esFinal(): bool
    {
        return in_array($this, [self::ENTREGADO, self::CANCELADO]);
    }
}