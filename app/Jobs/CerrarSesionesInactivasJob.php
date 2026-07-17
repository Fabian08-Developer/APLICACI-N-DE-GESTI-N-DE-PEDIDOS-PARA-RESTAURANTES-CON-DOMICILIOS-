<?php

namespace App\Jobs;

use App\Models\SesionCliente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Detecta y cierra sesiones de mesa que llevan demasiado tiempo sin actividad.
 *
 * Flujo:
 *  1. Busca sesiones activas cuyo `actualizado_en` supera el timeout configurado.
 *  2. Para cada sesión llama a cerrar() que:
 *      - Cancela pedidos activos (estado zombie)
 *      - Marca activo = false
 *      - Llama mesa->liberar() si no quedan otras sesiones activas
 *  3. Registra en log el resultado.
 *
 * Se programa en el scheduler para correr cada 15 minutos:
 *   Schedule::job(new CerrarSesionesInactivasJob())->everyFifteenMinutes();
 *
 * El timeout se configura en .env:
 *   SESION_MESA_TIMEOUT_HORAS=4  (default: 4 horas)
 *
 * NOTA: Para sesiones de DOMICILIO el timeout es distinto (más largo)
 * ya que el cliente puede tardar más entre interacciones.
 */
class CerrarSesionesInactivasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 180;

    /** Horas de inactividad antes de cerrar una sesión local (mesa). */
    public const TIMEOUT_LOCAL_HORAS     = 4;

    /** Horas de inactividad antes de cerrar una sesión de domicilio. */
    public const TIMEOUT_DOMICILIO_HORAS = 8;

    public function handle(): void
    {
        $cerradas = 0;
        $errores  = 0;

        // ── Sesiones LOCAL (mesa): timeout más corto ─────────────────────
        $limiteLocal = now()->subHours(self::TIMEOUT_LOCAL_HORAS);

        $sesionesLocal = SesionCliente::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('activo', true)
            ->where('tipo', 'local')
            ->where(function ($q) use ($limiteLocal) {
                // Comparar contra ultima_actividad_en si existe, sino contra actualizado_en
                $q->where(function ($inner) use ($limiteLocal) {
                    $inner->whereNotNull('ultima_actividad_en')
                          ->where('ultima_actividad_en', '<', $limiteLocal);
                })->orWhere(function ($inner) use ($limiteLocal) {
                    $inner->whereNull('ultima_actividad_en')
                          ->where('actualizado_en', '<', $limiteLocal);
                });
            })
            // ⚠️ CRÍTICO: No cerrar si tiene pedidos activos en curso
            // Esto protege sesiones del mesero con pedidos EN_PREPARACION, LISTO, etc.
            ->whereDoesntHave('pedidos', function ($q) {
                $q->whereNotIn('estado', [
                    \App\Enums\EstadoPedido::ENTREGADO->value,
                    \App\Enums\EstadoPedido::CANCELADO->value,
                ]);
            })
            ->with(['mesa', 'pedidos'])
            ->get();


        foreach ($sesionesLocal as $sesion) {
            try {
                $sesion->cerrar();
                $cerradas++;
                logger()->info('[CerrarSesionesInactivas] Sesión local cerrada por inactividad.', [
                    'sesion_id'   => $sesion->id,
                    'mesa_id'     => $sesion->mesa_id,
                    'sucursal_id' => $sesion->sucursal_id,
                    'inactiva_desde' => $sesion->ultima_actividad_en ?? $sesion->actualizado_en,
                ]);
            } catch (\Exception $e) {
                $errores++;
                logger()->error('[CerrarSesionesInactivas] Error cerrando sesión local.', [
                    'sesion_id' => $sesion->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // ── Sesiones DOMICILIO: timeout más largo ─────────────────────────
        $limiteDomicilio = now()->subHours(self::TIMEOUT_DOMICILIO_HORAS);

        $sesionesDomicilio = SesionCliente::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('activo', true)
            ->where('tipo', 'domicilio')
            ->where(function ($q) use ($limiteDomicilio) {
                $q->where(function ($inner) use ($limiteDomicilio) {
                    $inner->whereNotNull('ultima_actividad_en')
                          ->where('ultima_actividad_en', '<', $limiteDomicilio);
                })->orWhere(function ($inner) use ($limiteDomicilio) {
                    $inner->whereNull('ultima_actividad_en')
                          ->where('actualizado_en', '<', $limiteDomicilio);
                });
            })
            ->with(['pedidos'])
            ->get();

        foreach ($sesionesDomicilio as $sesion) {
            try {
                $sesion->cerrar();
                $cerradas++;
                logger()->info('[CerrarSesionesInactivas] Sesión domicilio cerrada por inactividad.', [
                    'sesion_id'   => $sesion->id,
                    'sucursal_id' => $sesion->sucursal_id,
                ]);
            } catch (\Exception $e) {
                $errores++;
                logger()->error('[CerrarSesionesInactivas] Error cerrando sesión domicilio.', [
                    'sesion_id' => $sesion->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        if ($cerradas > 0 || $errores > 0) {
            logger()->info("[CerrarSesionesInactivas] Resultado: {$cerradas} sesiones cerradas, {$errores} errores.");
        }
    }
}
