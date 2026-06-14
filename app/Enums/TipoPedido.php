<?php

namespace App\Enums;

/**
 * Enum canónico para el tipo de pedido.
 *
 * Elimina los string literals 'local' y 'domicilio' dispersos en el código.
 *
 * Uso:
 *   $pedido->tipo === TipoPedido::DOMICILIO->value
 *   $query->where('tipo', TipoPedido::LOCAL->value)
 */
enum TipoPedido: string
{
    case LOCAL     = 'local';
    case DOMICILIO = 'domicilio';

    /**
     * Retorna la etiqueta legible para humanos.
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::LOCAL     => 'Mesa / Local',
            self::DOMICILIO => 'Domicilio',
        };
    }

    /**
     * Indica si el tipo de pedido requiere asignación de domiciliario.
     */
    public function requiereDomiciliario(): bool
    {
        return $this === self::DOMICILIO;
    }

    /**
     * Indica si el tipo de pedido requiere asignación de mesero.
     */
    public function requiereMesero(): bool
    {
        return $this === self::LOCAL;
    }
}
