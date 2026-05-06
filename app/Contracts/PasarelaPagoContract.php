<?php

namespace App\Contracts;

use Illuminate\Http\Request;

/**
 * Contrato que debe cumplir cualquier pasarela de pago.
 *
 * Si en el futuro se cambia de Wompi a Bold, PayU u otro proveedor,
 * solo se crea una nueva clase que implemente esta interfaz y se
 * actualiza el binding en AppServiceProvider — ningún controlador
 * necesita modificarse.
 */
interface PasarelaPagoContract
{
    /**
     * Inicia una transacción de pago por Nequi.
     *
     * @param  array{monto: float, telefono: string, pedido_id: int, email: string|null}  $datos
     * @return array{exito: bool, referencia: string|null, estado: string, mensaje: string}
     */
    public function crearTransaccionNequi(array $datos): array;

    /**
     * Verifica que el webhook recibido realmente venga del proveedor
     * y no sea un request malicioso.
     */
    public function verificarFirmaWebhook(Request $request): bool;

    /**
     * Traduce el estado del proveedor externo al estado interno del sistema.
     */
    public function mapearEstado(string $estadoExterno): string;
}