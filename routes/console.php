<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcesarNoShowsJob;
use App\Jobs\CancelarReservasExpiradasJob;

Schedule::command('accounts:cleanup-trash')->daily();

// Detectar reservas sin check-in y marcarlas como NO_SHOW cada 5 minutos
Schedule::job(new ProcesarNoShowsJob())->everyFiveMinutes();

// Cancelar reservas PENDIENTE_PAGO que llevan más de 24h sin pago (corre cada hora)
Schedule::job(new CancelarReservasExpiradasJob())->hourly()->name('cancelar-reservas-expiradas')->withoutOverlapping();
