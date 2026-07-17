<?php

namespace App\Jobs;

use App\Enums\EstadoReserva;
use App\Models\ReservaMesa;
use App\Services\ReservaService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Cancela automáticamente reservas que han superado sus límites de tiempo.
 *
 * Cubre 3 escenarios:
 *
 *  1. PENDIENTE_PAGO → CANCELADA
 *     El cliente no pagó el depósito en 24h desde que creó la reserva.
 *
 *  2. PENDIENTE → CANCELADA
 *     La reserva tiene el depósito pagado pero el restaurante no confirmó a tiempo,
 *     y la fecha/hora de la reserva ya pasó (incluyendo margen de tolerancia).
 *
 *  3. CONFIRMADA → NO_SHOW (fallback)
 *     Reserva confirmada cuya hora_fin ya pasó hace más de la tolerancia configurada
 *     y ProcesarNoShowsJob no la detectó (ej. no corrió). Se marca como NO_SHOW.
 *
 * Se programa en el scheduler para correr cada hora:
 *   Schedule::job(new CancelarReservasExpiradasJob())->hourly()->withoutOverlapping();
 */
class CancelarReservasExpiradasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 180;

    /** Horas de gracia para pagar el depósito (PENDIENTE_PAGO). */
    public const HORAS_GRACIA_PAGO = 24;

    /**
     * Minutos de tolerancia adicional después de hora_fin para considerar la reserva expirada.
     * Este margen evita falsos positivos por pequeñas diferencias de timezone.
     */
    public const MINUTOS_TOLERANCIA_FALLBACK = 30;

    public function handle(ReservaService $service): void
    {
        $canceladasPago       = 0;
        $canceladasPendiente  = 0;
        $noShowsFallback      = 0;

        // ── 1. PENDIENTE_PAGO vencidas (depósito nunca llegó) ─────────────
        $limitePago = now()->subHours(self::HORAS_GRACIA_PAGO);

        $reservasSinPago = ReservaMesa::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('estado', EstadoReserva::PENDIENTE_PAGO->value)
            ->where('creado_en', '<', $limitePago)
            ->with('sucursal')
            ->get();

        foreach ($reservasSinPago as $reserva) {
            try {
                $service->cancelarReserva(
                    $reserva,
                    'Depósito no recibido dentro de ' . self::HORAS_GRACIA_PAGO . ' horas. Reserva cancelada automáticamente.',
                    'sistema',
                    $reserva->sucursal
                );
                $canceladasPago++;
                logger()->info('[CancelarReservasExpiradas] PENDIENTE_PAGO cancelada.', [
                    'codigo'      => $reserva->codigo_reserva,
                    'sucursal_id' => $reserva->sucursal_id,
                ]);
            } catch (\Exception $e) {
                logger()->error('[CancelarReservasExpiradas] Error cancelando PENDIENTE_PAGO.', [
                    'id'    => $reserva->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ── 2. PENDIENTE pasadas (depósito pagado pero hora ya transcurrió) ─
        // Una reserva PENDIENTE sigue esperando confirmación manual del restaurante.
        // Si ya pasó la hora de la reserva + tolerancia, no tiene sentido confirmarla.
        $ahora = now();

        $reservasPendientePasadas = ReservaMesa::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('estado', EstadoReserva::PENDIENTE->value)
            ->whereDate('fecha_reserva', '<=', $ahora->toDateString())
            ->with('sucursal')
            ->get()
            ->filter(function (ReservaMesa $reserva) use ($ahora) {
                // La hora_fin de la reserva ya pasó (considerando tolerancia)
                $horaFinConTolerancias = $reserva->fin->addMinutes(self::MINUTOS_TOLERANCIA_FALLBACK);
                return $ahora->gt($horaFinConTolerancias);
            });

        foreach ($reservasPendientePasadas as $reserva) {
            try {
                $service->cancelarReserva(
                    $reserva,
                    'La hora de la reserva ya transcurrió sin ser confirmada por el restaurante. Cancelada automáticamente.',
                    'sistema',
                    $reserva->sucursal
                );
                $canceladasPendiente++;
                logger()->info('[CancelarReservasExpiradas] PENDIENTE pasada cancelada.', [
                    'codigo'      => $reserva->codigo_reserva,
                    'fecha'       => $reserva->fecha_reserva->toDateString(),
                    'hora_fin'    => $reserva->hora_fin,
                    'sucursal_id' => $reserva->sucursal_id,
                ]);
            } catch (\Exception $e) {
                logger()->error('[CancelarReservasExpiradas] Error cancelando PENDIENTE pasada.', [
                    'id'    => $reserva->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ── 3. CONFIRMADA pasada sin check-in (fallback de ProcesarNoShowsJob) ─
        // Si ProcesarNoShowsJob no corrió o falló, esta sección actúa como respaldo.
        // Detecta reservas CONFIRMADAS cuya hora_fin ya pasó hace más de la tolerancia.
        $reservasConfirmadasPasadas = ReservaMesa::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('estado', EstadoReserva::CONFIRMADA->value)
            ->whereDate('fecha_reserva', '<=', $ahora->toDateString())
            ->with('sucursal')
            ->get()
            ->filter(function (ReservaMesa $reserva) use ($ahora) {
                $horaFinConTolerancias = $reserva->fin->addMinutes(self::MINUTOS_TOLERANCIA_FALLBACK);
                return $ahora->gt($horaFinConTolerancias);
            });

        foreach ($reservasConfirmadasPasadas as $reserva) {
            try {
                // Marcar como NO_SHOW (no como cancelada — para estadísticas correctas)
                $reserva->marcarNoShow();
                $noShowsFallback++;
                logger()->warning('[CancelarReservasExpiradas] CONFIRMADA marcada NO_SHOW (fallback).', [
                    'codigo'      => $reserva->codigo_reserva,
                    'fecha'       => $reserva->fecha_reserva->toDateString(),
                    'hora_fin'    => $reserva->hora_fin,
                    'sucursal_id' => $reserva->sucursal_id,
                ]);
            } catch (\Exception $e) {
                logger()->error('[CancelarReservasExpiradas] Error marcando NO_SHOW fallback.', [
                    'id'    => $reserva->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ── Resumen en log ────────────────────────────────────────────────
        $total = $canceladasPago + $canceladasPendiente + $noShowsFallback;
        if ($total > 0) {
            logger()->info('[CancelarReservasExpiradas] Resumen del ciclo.', [
                'canceladas_sin_pago'     => $canceladasPago,
                'canceladas_pendiente'    => $canceladasPendiente,
                'no_shows_fallback'       => $noShowsFallback,
                'total'                   => $total,
            ]);
        }
    }
}
