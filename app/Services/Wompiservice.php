<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WompiService
{
    private string $baseUrl;
    private string $publicKey;
    private string $privateKey;
    private string $eventsSecret;

    public function __construct()
    {
        $this->baseUrl      = config('wompi.base_url');
        $this->publicKey    = config('wompi.public_key');
        $this->privateKey   = config('wompi.private_key');
        $this->eventsSecret = config('wompi.events_secret');
    }

    // -------------------------------------------------------------------------
    // CREAR TRANSACCIÓN NEQUI
    // -------------------------------------------------------------------------
    /**
     * Inicia una transacción de pago por Nequi.
     *
     * Flujo real de Wompi Nequi:
     *   1. GET  /merchants/{publicKey}            → obtener acceptance_token
     *   2. POST /transactions                     → crear transacción (tipo NEQUI_PUSH)
     *   3. Wompi envía push al número; el cliente acepta en la app
     *   4. Wompi notifica al webhook con el resultado final
     *
     * En sandbox (simulación) retornamos una referencia generada localmente
     * para poder ejercitar todo el flujo sin llaves reales.
     *
     * @param  array{monto: float, telefono: string, pedido_id: int}  $datos
     * @return array{exito: bool, referencia: string|null, estado: string, mensaje: string}
     */
    public function crearTransaccionNequi(array $datos): array
    {
        // ── Modo simulación (sin llaves configuradas o entorno local) ──────────
        if ($this->esModoSimulacion()) {
            return $this->simularCreacionTransaccion($datos);
        }

        // ── Modo real (sandbox o producción con llaves configuradas) ──────────
        try {
            // Paso 1: obtener acceptance_token
            $merchantResponse = Http::get("{$this->baseUrl}/merchants/{$this->publicKey}");

            if ($merchantResponse->failed()) {
                Log::error('Wompi: No se pudo obtener acceptance_token', [
                    'status' => $merchantResponse->status(),
                ]);
                return $this->respuestaError('No se pudo conectar con Wompi. Intenta de nuevo.');
            }

            $acceptanceToken = $merchantResponse->json('data.presigned_acceptance.acceptance_token');

            // Paso 2: crear transacción
            $referencia = $this->generarReferencia($datos['pedido_id']);

            $transaccionResponse = Http::withToken($this->privateKey)
                ->post("{$this->baseUrl}/transactions", [
                    'acceptance_token'   => $acceptanceToken,
                    'amount_in_cents'    => (int) round($datos['monto'] * 100),
                    'currency'           => 'COP',
                    'customer_email'     => $datos['email'] ?? 'cliente@restaurante.com',
                    'reference'          => $referencia,
                    'payment_method'     => [
                        'type'             => 'NEQUI',
                        'phone_number'     => $datos['telefono'],
                    ],
                ]);

            if ($transaccionResponse->failed()) {
                $errorMsg = $transaccionResponse->json('error.messages.0')
                    ?? 'Error al procesar el pago con Nequi.';

                Log::warning('Wompi: Transacción rechazada', [
                    'pedido_id' => $datos['pedido_id'],
                    'respuesta' => $transaccionResponse->json(),
                ]);

                return $this->respuestaError($errorMsg);
            }

            $transaccion = $transaccionResponse->json('data');

            Log::info('Wompi: Transacción Nequi creada', [
                'pedido_id'  => $datos['pedido_id'],
                'referencia' => $referencia,
                'wompi_id'   => $transaccion['id'] ?? null,
            ]);

            return [
                'exito'      => true,
                'referencia' => $referencia,
                'estado'     => 'PENDING',
                'mensaje'    => 'Notificación enviada al número ' . $datos['telefono'],
            ];

        } catch (\Throwable $e) {
            Log::error('Wompi: Excepción al crear transacción', [
                'mensaje'   => $e->getMessage(),
                'pedido_id' => $datos['pedido_id'] ?? null,
            ]);

            return $this->respuestaError('Ocurrió un error inesperado. Por favor intenta de nuevo.');
        }
    }

    // -------------------------------------------------------------------------
    // VERIFICAR FIRMA DEL WEBHOOK
    // -------------------------------------------------------------------------
    /**
     * Wompi firma cada evento así:
     *   checksum = SHA256( timestamp + events_secret + properties_string )
     * El checksum viaja en el header  X-Event-Checksum.
     * El timestamp viaja en el cuerpo  $.timestamp
     *
     * Documentación: https://docs.wompi.co/docs/colombia/eventos-de-pago
     */
    public function verificarFirmaWebhook(Request $request): bool
    {
        // En modo simulación, aceptar siempre
        if ($this->esModoSimulacion()) {
            return true;
        }

        $checksumRecibido = $request->header('X-Event-Checksum');

        if (empty($checksumRecibido) || empty($this->eventsSecret)) {
            Log::warning('Wompi Webhook: header X-Event-Checksum ausente o events_secret no configurado');
            return false;
        }

        $timestamp  = $request->input('timestamp', '');
        $properties = $request->input('properties', []);

        // Concatenar los valores de properties en el orden que los envía Wompi
        $propertiesString = implode('', array_values($properties));
        $cadena           = $timestamp . $this->eventsSecret . $propertiesString;
        $checksumEsperado = hash('sha256', $cadena);

        return hash_equals($checksumEsperado, $checksumRecibido);
    }

    // -------------------------------------------------------------------------
    // MAPEO DE ESTADOS
    // -------------------------------------------------------------------------
    /**
     * Convierte el estado de Wompi al estado interno del sistema.
     *
     * Estados Wompi: PENDING | APPROVED | DECLINED | VOIDED | ERROR
     */
    public function mapearEstado(string $estadoWompi): string
    {
        return match (strtoupper($estadoWompi)) {
            'APPROVED'         => 'COMPLETADO',
            'DECLINED', 'ERROR'=> 'FALLIDO',
            'VOIDED'           => 'REEMBOLSADO',
            default            => 'PENDIENTE',
        };
    }

    // -------------------------------------------------------------------------
    // HELPERS PRIVADOS
    // -------------------------------------------------------------------------

    private function esModoSimulacion(): bool
    {
        return empty($this->privateKey) || app()->environment('local', 'testing');
    }

    private function generarReferencia(int $pedidoId): string
    {
        // Formato: WMP-{pedido_id}-{random} para facilitar rastreo
        return 'WMP-' . $pedidoId . '-' . strtoupper(Str::random(8));
    }

    private function simularCreacionTransaccion(array $datos): array
    {
        $referencia = $this->generarReferencia($datos['pedido_id']);

        Log::info('Wompi SIMULACIÓN: Transacción Nequi creada', [
            'pedido_id'  => $datos['pedido_id'],
            'telefono'   => $datos['telefono'],
            'referencia' => $referencia,
        ]);

        return [
            'exito'      => true,
            'referencia' => $referencia,
            'estado'     => 'PENDING',
            'mensaje'    => '[SIMULACIÓN] Notificación enviada al número ' . $datos['telefono'],
        ];
    }

    private function respuestaError(string $mensaje): array
    {
        return [
            'exito'      => false,
            'referencia' => null,
            'estado'     => 'ERROR',
            'mensaje'    => $mensaje,
        ];
    }
}