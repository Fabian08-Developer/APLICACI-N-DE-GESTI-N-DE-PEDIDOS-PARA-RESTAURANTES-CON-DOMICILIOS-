<?php

namespace App\Console\Commands;

use App\Models\Sesion;
use Illuminate\Console\Command;

/**
 * Limpia sesiones de usuario (tabla `sesiones`) cuya `fecha_expiracion`
 * ya pasó o que llevan más de 30 días inactivas.
 *
 * Esto evita que la tabla crezca indefinidamente con registros muertos.
 *
 * Programado en scheduler para correr diariamente:
 *   Schedule::command('sessions:cleanup')->daily();
 */
class CleanupExpiredUserSessions extends Command
{
    protected $signature   = 'sessions:cleanup';
    protected $description = 'Elimina las sesiones de usuario expiradas o inactivas de la tabla `sesiones`';

    /** Días de gracia adicionales después de `fecha_expiracion` antes de eliminar. */
    public const DIAS_GRACIA = 7;

    public function handle(): int
    {
        // 1. Sesiones con fecha_expiracion definida que ya vencieron
        $porExpiracion = Sesion::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->whereNotNull('fecha_expiracion')
            ->where('fecha_expiracion', '<', now()->subDays(self::DIAS_GRACIA))
            ->delete();

        // 2. Sesiones sin fecha de expiración que llevan +30 días sin actividad
        $porAntiguedad = Sesion::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->whereNull('fecha_expiracion')
            ->where('activa', false)
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();

        $total = $porExpiracion + $porAntiguedad;

        if ($total > 0) {
            $this->info("Limpieza completada: {$total} sesiones eliminadas ({$porExpiracion} por expiración, {$porAntiguedad} por antigüedad).");
            logger()->info('[CleanupExpiredUserSessions] Sesiones eliminadas.', [
                'por_expiracion' => $porExpiracion,
                'por_antiguedad' => $porAntiguedad,
                'total'          => $total,
            ]);
        } else {
            $this->line('No hay sesiones de usuario expiradas para limpiar.');
        }

        return self::SUCCESS;
    }
}
