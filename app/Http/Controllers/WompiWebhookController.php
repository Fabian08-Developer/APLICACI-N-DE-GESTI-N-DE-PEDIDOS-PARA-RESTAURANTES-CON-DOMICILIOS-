<?php

namespace App\Http\Controllers;

use App\Mail\PagoAprobadoMail;
use App\Mail\PagoFallidoMail;
use App\Models\HistorialEstadoPedido;
use App\Models\Pago;
use App\Services\WompiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WompiWebhookController extends Controller
{
    public function __construct(private readonly WompiService $wompi) {}

    /*
    |--------------------------------------------------------------------------
    | WEBHOOK — Wompi notifica el resultado de una transacción
    | Ruta: POST /wompi/webhook   (sin CSRF — ver web.php)
    |--------------------------------------------------------------------------
    */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verificar firma
        if (!$this->wompi->verificarFirmaWebhook($request)) {
            Log::warning('Wompi Webhook: firma inválida', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Firma inválida'], 401);
        }

        $evento = $request->input('event');

        if ($evento !== 'transaction.updated') {
            return response()->json(['ok' => true, 'msg' => "Evento '{$evento}' ignorado"]);
        }

        $transaccion = $request->input('data.transaction', []);
        $referencia  = $transaccion['reference'] ?? null;
        $estadoWompi = $transaccion['status']    ?? null;

        if (! $referencia || ! $estadoWompi) {
            Log::error('Wompi Webhook: payload incompleto', ['payload' => $request->all()]);
            return response()->json(['error' => 'Datos incompletos'], 422);
        }

        // 2. Buscar el pago por referencia
        $pago = Pago::with('pedido.detalles.producto')
            ->where('referencia_transaccion', $referencia)
            ->first();

        if (! $pago) {
            Log::warning('Wompi Webhook: referencia no encontrada', ['referencia' => $referencia]);
            return response()->json(['ok' => true, 'msg' => 'Referencia no encontrada']);
        }

        // 3. Idempotencia — no reprocesar estados finales
        if (in_array($pago->estado, ['COMPLETADO', 'FALLIDO', 'REEMBOLSADO'])) {
            return response()->json(['ok' => true, 'msg' => 'Pago ya procesado']);
        }

        // 4. Actualizar estado de forma atómica
        $estadoInterno = $this->wompi->mapearEstado($estadoWompi);

        try {
            DB::transaction(function () use ($pago, $estadoInterno) {
                $pago->update(['estado' => $estadoInterno]);

                if ($estadoInterno === 'COMPLETADO') {
                    $pago->pedido->update(['estado' => 'EN_COCINA']);
                    HistorialEstadoPedido::create([
                        'pedido_id' => $pago->pedido_id,
                        'estado'    => 'EN_COCINA',
                        'fecha'     => now(),
                    ]);
                }

                if ($estadoInterno === 'FALLIDO') {
                    $pago->pedido->update(['estado' => 'CREADO']);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Wompi Webhook: error al actualizar pago', [
                'pago_id' => $pago->id,
                'error'   => $e->getMessage(),
            ]);
            // Retornar 500 para que Wompi reintente automáticamente
            return response()->json(['error' => 'Error interno'], 500);
        }

        // 5. Email FUERA de la transacción DB — un fallo de email
        //    no debe revertir el pago ya procesado
        $this->enviarEmailNotificacion($pago->fresh('pedido.detalles.producto'), $estadoInterno);

        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Enviar email según el resultado del pago
    |--------------------------------------------------------------------------
    */
    private function enviarEmailNotificacion(Pago $pago, string $estado): void
    {
        if (empty($pago->email)) {
            return;
        }

        try {
            match ($estado) {
                'COMPLETADO' => Mail::to($pago->email)->send(new PagoAprobadoMail($pago)),
                'FALLIDO'    => Mail::to($pago->email)->send(new PagoFallidoMail($pago)),
                default      => null,
            };

            Log::info("Email '{$estado}' enviado", [
                'pago_id' => $pago->id,
                'email'   => $pago->email,
            ]);
        } catch (\Throwable $e) {
            // Un fallo de email NO interrumpe el flujo del pago
            Log::error('Error al enviar email de notificación', [
                'pago_id' => $pago->id,
                'email'   => $pago->email,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
