<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcesarNoShowsJob;
use App\Jobs\CancelarReservasExpiradasJob;
use App\Jobs\CerrarSesionesInactivasJob;
use App\Jobs\CancelarPedidosZombieJob;

// ─── RESERVAS ─────────────────────────────────────────────────────────────────

// Detectar reservas confirmadas sin check-in y marcarlas como NO_SHOW
// Corre cada 5 minutos para capturar no-shows casi en tiempo real.
Schedule::job(new ProcesarNoShowsJob())->everyFiveMinutes();

// Cancelar reservas vencidas (PENDIENTE_PAGO, PENDIENTE pasada, CONFIRMADA sin check-in fallback)
// Corre cada hora con protección anti-solapamiento.
Schedule::job(new CancelarReservasExpiradasJob())
    ->hourly()
    ->name('cancelar-reservas-expiradas')
    ->withoutOverlapping();

// ─── SESIONES DE MESA ─────────────────────────────────────────────────────────

// Cerrar sesiones de mesa (local y domicilio) que llevan horas sin actividad.
// Libera automáticamente las mesas ocupadas por sesiones zombie.
// Corre cada 15 minutos.
Schedule::job(new CerrarSesionesInactivasJob())
    ->everyFifteenMinutes()
    ->name('cerrar-sesiones-inactivas')
    ->withoutOverlapping();

// ─── PEDIDOS ──────────────────────────────────────────────────────────────────

// Detectar y cancelar pedidos "zombie" que permanecen en estados intermedios
// más tiempo del razonable para su tipo y estado.
// Corre cada 10 minutos.
Schedule::job(new CancelarPedidosZombieJob())
    ->everyTenMinutes()
    ->name('cancelar-pedidos-zombie')
    ->withoutOverlapping();

// ─── LIMPIEZA GENERAL ─────────────────────────────────────────────────────────

// Eliminar cuentas de gerentes en la papelera con más de 30 días
Schedule::command('accounts:cleanup-trash')->daily();

// Limpiar sesiones de usuario (tabla `sesiones`) expiradas o antiguas
Schedule::command('sessions:cleanup')->daily()->at('03:00');
