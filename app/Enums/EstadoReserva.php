<?php

namespace App\Enums;

/**
 * Estados del ciclo de vida de una Reserva de Mesa.
 *
 * Flujo principal:
 *   PENDIENTE → CONFIRMADA → CLIENTE_LLEGO → COMPLETADA
 *        │           │
 *        └───────────┴──→ CANCELADA
 *                          NO_SHOW (automático por job)
 */
enum EstadoReserva: string
{
    case PENDIENTE_PAGO  = 'pendiente_pago';  // Esperando pago del depósito
    case PENDIENTE      = 'pendiente';         // Depósito pagado, esperando confirmación del restaurante
    case CONFIRMADA     = 'confirmada';
    case CLIENTE_LLEGO  = 'cliente_llego';
    case COMPLETADA     = 'completada';
    case CANCELADA      = 'cancelada';
    case NO_SHOW        = 'no_show';

    /**
     * Etiqueta legible en español.
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::PENDIENTE_PAGO => 'Pago de depósito pendiente',
            self::PENDIENTE     => 'Pendiente de confirmación',
            self::CONFIRMADA    => 'Confirmada',
            self::CLIENTE_LLEGO => 'Cliente llegó',
            self::COMPLETADA    => 'Completada',
            self::CANCELADA     => 'Cancelada',
            self::NO_SHOW       => 'No se presentó',
        };
    }

    /**
     * Color CSS para badges visuales (clase Tailwind / variable CSS).
     */
    public function colorClase(): string
    {
        return match ($this) {
            self::PENDIENTE_PAGO => 'badge-pago-pendiente',
            self::PENDIENTE     => 'badge-pendiente',
            self::CONFIRMADA    => 'badge-confirmada',
            self::CLIENTE_LLEGO => 'badge-llegada',
            self::COMPLETADA    => 'badge-completada',
            self::CANCELADA     => 'badge-cancelada',
            self::NO_SHOW       => 'badge-no-show',
        };
    }

    /**
     * Indica si el estado es final (no puede cambiar).
     */
    public function esFinal(): bool
    {
        return in_array($this, [
            self::COMPLETADA,
            self::CANCELADA,
            self::NO_SHOW,
        ]);
    }

    /**
     * Indica si la reserva está activa (ocupa el slot de la mesa).
     */
    public function estaActiva(): bool
    {
        return in_array($this, [
            self::PENDIENTE,
            self::CONFIRMADA,
            self::CLIENTE_LLEGO,
        ]);
    }

    /**
     * Transiciones válidas desde este estado.
     */
    public function transicionesValidas(): array
    {
        return match ($this) {
            self::PENDIENTE_PAGO => [self::PENDIENTE, self::CONFIRMADA, self::CANCELADA],
            self::PENDIENTE     => [self::CONFIRMADA, self::CANCELADA],
            self::CONFIRMADA    => [self::CLIENTE_LLEGO, self::CANCELADA, self::NO_SHOW],
            self::CLIENTE_LLEGO => [self::COMPLETADA, self::CANCELADA],
            self::COMPLETADA    => [],
            self::CANCELADA     => [],
            self::NO_SHOW       => [],
        };
    }

    /**
     * Verifica si se puede transicionar al estado objetivo.
     */
    public function puedeTransicionarA(self $objetivo): bool
    {
        return in_array($objetivo, $this->transicionesValidas());
    }
}
