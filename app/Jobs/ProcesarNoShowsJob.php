<?php

namespace App\Jobs;

use App\Models\Sucursal;
use App\Services\ReservaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job programado para detectar reservas confirmadas que han expirado sin check-in.
 *
 * Debe registrarse en el scheduler para correr cada 5 minutos:
 *   $schedule->job(new ProcesarNoShowsJob())->everyFiveMinutes();
 */
class ProcesarNoShowsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 120;

    public function handle(ReservaService $service): void
    {
        $sucursales = Sucursal::where('activo', true)->get();

        foreach ($sucursales as $sucursal) {
            try {
                $cantidad = $service->procesarNoShows($sucursal);

                if ($cantidad > 0) {
                    logger()->info("ProcesarNoShowsJob: {$cantidad} no-shows procesados en sucursal {$sucursal->nombre}");
                }
            } catch (\Exception $e) {
                logger()->error("ProcesarNoShowsJob error en sucursal {$sucursal->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
