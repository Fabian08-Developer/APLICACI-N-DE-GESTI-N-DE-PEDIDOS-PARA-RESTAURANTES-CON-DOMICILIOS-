<?php

namespace App\Traits;

use App\Enums\EstadoPago;
use App\Mail\PagoAprobadoMail;
use App\Mail\PagoFallidoMail;
use App\Mail\PedidoCanceladoMail;
use App\Mail\ReembolsoMail;
use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait OrderEmailNotifications
{
    /**
     * Envía email de resultado de pago (Aprobado o Fallido)
     */
    protected function enviarEmailPago(Pago $pago, string $estado): void
    {
        if (empty($pago->email)) return;

        try {
            match ($estado) {
                EstadoPago::COMPLETADO->value => Mail::to($pago->email)->send(new PagoAprobadoMail($pago)),
                EstadoPago::FALLIDO->value    => Mail::to($pago->email)->send(new PagoFallidoMail($pago)),
                default                       => null,
            };
        } catch (\Throwable $e) {
            Log::error('Error al enviar email de pago', [
                'pago_id' => $pago->id,
                'error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Envía email de cancelación de pedido
     */
    protected function enviarEmailCancelacion(Pedido $pedido): void
    {
        // Buscamos el email en los pagos asociados (el cliente ingresa el email para pagar)
        $email = $pedido->pagos()->whereNotNull('email')->value('email');
        if (empty($email)) return;

        try {
            Mail::to($email)->send(new PedidoCanceladoMail($pedido));
        } catch (\Throwable $e) {
            Log::error('Error al enviar email de cancelación', [
                'pedido_id' => $pedido->id,
                'error'     => $e->getMessage()
            ]);
        }
    }

    /**
     * Envía email de reembolso
     */
    protected function enviarEmailReembolso(Pedido $pedido): void
    {
        $pago = $pedido->pagos()
            ->where('estado', EstadoPago::REEMBOLSADO->value)
            ->whereNotNull('email')
            ->first();

        if (!$pago) return;

        try {
            Mail::to($pago->email)->send(new ReembolsoMail($pedido, $pago));
        } catch (\Throwable $e) {
            Log::error('Error al enviar email de reembolso', [
                'pedido_id' => $pedido->id,
                'error'     => $e->getMessage()
            ]);
        }
    }
}
