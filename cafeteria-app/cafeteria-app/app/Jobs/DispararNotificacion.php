<?php

namespace App\Jobs;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job orquestador de notificaciones.
 *
 * Responsabilidades:
 *   1. Persistir la notificación en la tabla `notificaciones` (BD).
 *   2. Guardar para el usuario específico O para todos los usuarios relevantes
 *      de la sucursal (admins + meseros + cocina).
 *
 * El broadcast en tiempo real lo hacen los Eventos directamente (ShouldBroadcast).
 * Este Job solo maneja la persistencia para el historial/campanilla.
 */
class DispararNotificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 5;

    public function __construct(
        private readonly string  $sucursal_id,
        private readonly string  $tipo,
        private readonly string  $titulo,
        private readonly string  $mensaje,
        private readonly array   $datos       = [],
        private readonly ?string $usuario_id  = null, // null = guardar para todos en la sucursal
    ) {}

    public function handle(): void
    {
        try {
            if ($this->usuario_id) {
                // Notificación personal — solo para este usuario
                Notificacion::create([
                    'usuario_id' => $this->usuario_id,
                    'tipo'       => $this->tipo,
                    'titulo'     => $this->titulo,
                    'mensaje'    => $this->mensaje,
                    'datos'      => $this->datos,
                    'leida'      => false,
                ]);
            } else {
                // Notificación de sucursal — para todos los roles del panel admin
                $usuarios = User::where('sucursal_id', $this->sucursal_id)
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', [
                        'administrador', 'mesero', 'cocina',
                    ]))
                    ->where('activo', true)
                    ->get();

                foreach ($usuarios as $usuario) {
                    Notificacion::create([
                        'usuario_id' => $usuario->id,
                        'tipo'       => $this->tipo,
                        'titulo'     => $this->titulo,
                        'mensaje'    => $this->mensaje,
                        'datos'      => $this->datos,
                        'leida'      => false,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('[DispararNotificacion] Error al guardar notificación', [
                'sucursal_id' => $this->sucursal_id,
                'tipo'        => $this->tipo,
                'error'       => $e->getMessage(),
            ]);

            throw $e; // Re-lanzar para que el worker reintente
        }
    }

    /**
     * Factory method: crear notificación para toda la sucursal.
     */
    public static function paraSucursal(
        string $sucursal_id,
        string $tipo,
        string $titulo,
        string $mensaje,
        array  $datos = [],
    ): static {
        return new static($sucursal_id, $tipo, $titulo, $mensaje, $datos, null);
    }

    /**
     * Factory method: crear notificación personal para un usuario.
     */
    public static function paraUsuario(
        string $sucursal_id,
        string $usuario_id,
        string $tipo,
        string $titulo,
        string $mensaje,
        array  $datos = [],
    ): static {
        return new static($sucursal_id, $tipo, $titulo, $mensaje, $datos, $usuario_id);
    }
}
