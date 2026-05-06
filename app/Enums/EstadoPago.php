<?php

namespace App\Enums;

enum EstadoPago: string
{
    case PENDIENTE   = 'PENDIENTE';
    case COMPLETADO  = 'COMPLETADO';
    case FALLIDO     = 'FALLIDO';
    case REEMBOLSADO = 'REEMBOLSADO';
    case CANCELADO = 'CANCELADO';

    /**
     * Retorna true si el pago ya está en un estado final
     * que no debe ser reprocesado (idempotencia en webhook).
     */
    public function esFinal(): bool
    {
        return in_array($this, [
            self::COMPLETADO,
            self::FALLIDO,
            self::REEMBOLSADO,
            self::CANCELADO,
        ]);
    }

    /**
     * Convierte el estado de Wompi al estado interno del sistema.
     * Estados Wompi: PENDING | APPROVED | DECLINED | VOIDED | ERROR
     */
    public static function desdeWompi(string $estadoWompi): self
    {
        return match (strtoupper($estadoWompi)) {
            'APPROVED'          => self::COMPLETADO,
            'DECLINED', 'ERROR' => self::FALLIDO,
            'VOIDED'            => self::REEMBOLSADO,
            default             => self::PENDIENTE,
        };
    }
}