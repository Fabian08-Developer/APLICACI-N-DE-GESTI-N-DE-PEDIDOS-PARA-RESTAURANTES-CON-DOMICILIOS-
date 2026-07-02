<?php

namespace App\Jobs;

use App\Enums\EstadoReserva;
use App\Models\ReservaMesa;
use App\Services\ReservaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Cancela automaticamente las reservas PENDIENTE_PAGO con mas de 24h sin pago.
 * Se programa en el scheduler para correr cada hora.
 */
class CancelarReservasExpiradasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 120;

    public const HORAS_GRACIA = 24;

    public function handle(ReservaService $service): void
    {
        $limite = now()->subHours(self::HORAS_GRACIA);

        $reservasExpiradas = ReservaMesa::where('estado', EstadoReserva::PENDIENTE_PAGO->value)
            ->where('creado_en', '<', $limite)
            ->with('sucursal')
            ->get();

        foreach ($reservasExpiradas as $reserva) {
            try {
                $service->cancelarReserva(
                    $reserva,
                    'Deposito no recibido en ' . self::HORAS_GRACIA . ' horas.',
                    'sistema',
                    $reserva->sucursal
                );
                logger()->info('Reserva expirada cancelada.', ['codigo' => $reserva->codigo_reserva]);
            } catch (\Exception $e) {
                logger()->error('Error cancelando reserva expirada.', ['id' => $reserva->id, 'error' => $e->getMessage()]);
            }
        }

        if ($reservasExpiradas->count() > 0) {
            logger()->info('CancelarReservasExpiradasJob: ' . $reservasExpiradas->count() . ' reservas canceladas.');
        }
    }
}
