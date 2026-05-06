<?php

namespace App\Console\Commands;

use App\Models\SubSesion;
use App\Models\Usuario;
use Illuminate\Console\Command;

class LimpiarClientesTemporales extends Command
{
    protected $signature   = 'clientes:limpiar';
    protected $description = 'Elimina usuarios temporales y sub sesiones inactivas de más de 15 minutos';

    public function handle(): void
    {
        $limite = now()->subMinutes(15);

        // 1. Sub sesiones activas que llevan más de 15 min sin actividad
        $subSesionesViejas = SubSesion::where('estado', 'ACTIVA')
                                      ->where('fecha_inicio', '<', $limite)
                                      ->get();

        $count = 0;

        foreach ($subSesionesViejas as $sub) {
            // Cerramos la sub sesión
            $sub->update(['estado' => 'CERRADA', 'fecha_fin' => now()]);

            // Borramos el usuario temporal asociado
            $usuario = Usuario::find($sub->cliente_id);

            if ($usuario
                && str_starts_with($usuario->email, 'temp_')
                && str_ends_with($usuario->email, '@cliente.temp')
            ) {
                $usuario->delete();
                $count++;
            }
        }

        // 2. Usuarios temporales huérfanos (sin sub sesión o con sub sesión cerrada)
        $huerfanos = Usuario::whereHas('rol', fn($q) => $q->where('nombre', 'cliente'))
                            ->where('email', 'like', 'temp_%@cliente.temp')
                            ->where('created_at', '<', $limite)
                            ->get();

        foreach ($huerfanos as $usuario) {
            $usuario->delete();
            $count++;
        }

        $this->info("✓ {$count} clientes temporales eliminados.");
    }
}