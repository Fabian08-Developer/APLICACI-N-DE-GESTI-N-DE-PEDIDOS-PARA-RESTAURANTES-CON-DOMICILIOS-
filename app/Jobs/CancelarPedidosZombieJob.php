<?php

namespace App\Jobs;

use App\Enums\EstadoPedido;
use App\Models\HistorialEstadoPedido;
use App\Models\Pedido;
use App\Models\Sucursal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Jobs\DispararNotificacion;

/**
 * Detecta y cancela pedidos que llevan demasiado tiempo en un estado intermedio.
 *
 * "Pedido zombie" = pedido activo que no avanzó dentro del tiempo razonable
 * para su estado y tipo, y que por tanto satura el sistema con información basura.
 *
 * Timeouts configurados:
 *  - CREADO (nuevo, sin atender)           → 45 min para local | 60 min para domicilio
 *  - EN_PREPARACION (cocina)               → 90 min
 *  - LISTO (sin entregar / sin recoger)    → 30 min para local | 60 min para domicilio
 *  - ASIGNADO (domiciliario no salió)      → 45 min
 *  - EN_CAMINO (domiciliario tardó mucho)  → 180 min (3 horas)
 *
 * Se programa en el scheduler para correr cada 10 minutos:
 *   Schedule::job(new CancelarPedidosZombieJob())->everyTenMinutes();
 */
class CancelarPedidosZombieJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 180;

    /** Minutos máximos en cada estado antes de considerarlo zombie. */
    private const TIMEOUTS = [
        // [estado => minutos_max]
        // Para pedidos locales
        'local' => [
            EstadoPedido::CREADO->value          => 45,
            EstadoPedido::EN_PREPARACION->value   => 90,
            EstadoPedido::LISTO->value            => 30,
        ],
        // Para pedidos de domicilio
        'domicilio' => [
            EstadoPedido::CREADO->value          => 60,
            EstadoPedido::EN_PREPARACION->value   => 90,
            EstadoPedido::LISTO->value            => 60,
            EstadoPedido::ASIGNADO->value         => 45,
            EstadoPedido::EN_CAMINO->value        => 180,
        ],
    ];

    public function handle(): void
    {
        $cancelados = 0;
        $errores    = 0;

        foreach (self::TIMEOUTS as $tipo => $estados) {
            foreach ($estados as $estado => $minutos) {
                $limite = now()->subMinutes($minutos);

                $pedidos = Pedido::withoutGlobalScope(\App\Scopes\TenantScope::class)
                    ->where('tipo', $tipo)
                    ->where('estado', $estado)
                    ->where('actualizado_en', '<', $limite)
                    ->get();

                foreach ($pedidos as $pedido) {
                    try {
                        DB::transaction(function () use ($pedido, $estado, $tipo, $minutos) {
                            $motivo = "Cancelado automáticamente por timeout del sistema. "
                                . "El pedido permaneció en estado '{$estado}' por más de {$minutos} minutos sin avanzar.";

                            $pedido->update([
                                'estado'             => EstadoPedido::CANCELADO->value,
                                'motivo_cancelacion' => $motivo,
                            ]);

                            HistorialEstadoPedido::create([
                                'pedido_id'   => $pedido->id,
                                'sucursal_id' => $pedido->sucursal_id,
                                'estado'      => EstadoPedido::CANCELADO->value,
                                'usuario_id'  => null, // sistema automático
                                'cambiado_en' => now(),
                            ]);
                        });

                        $cancelados++;
                        logger()->warning('[CancelarPedidosZombie] Pedido zombie cancelado.', [
                            'pedido_id'   => $pedido->id,
                            'short_id'    => $pedido->short_id,
                            'tipo'        => $tipo,
                            'estado_prev' => $estado,
                            'minutos_max' => $minutos,
                            'sucursal_id' => $pedido->sucursal_id,
                        ]);

                        // Notificar al admin/mesero via WebPush (si el servicio está disponible)
                        $this->notificarZombie($pedido, $estado, $minutos);

                    } catch (\Exception $e) {
                        $errores++;
                        logger()->error('[CancelarPedidosZombie] Error cancelando pedido zombie.', [
                            'pedido_id' => $pedido->id,
                            'error'     => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        if ($cancelados > 0 || $errores > 0) {
            logger()->info("[CancelarPedidosZombie] Resultado: {$cancelados} cancelados, {$errores} errores.");
        }
    }

    /**
     * Envía notificación interna al personal de la sucursal sobre el pedido zombie.
     */
    private function notificarZombie(Pedido $pedido, string $estado, int $minutos): void
    {
        try {
            DispararNotificacion::dispatch(
                sucursal_id: $pedido->sucursal_id,
                tipo:        'pedido_cancelado',
                titulo:      "Pedido #{$pedido->short_id} cancelado por timeout",
                mensaje:     "El pedido permaneció en '{$estado}' por más de {$minutos} min. Fue cancelado automáticamente.",
                datos:       ['pedido_id' => $pedido->id, 'tipo' => 'timeout'],
            );
        } catch (\Exception $e) {
            // La notificación no es crítica — no relanzar
            logger()->warning('[CancelarPedidosZombie] No se pudo notificar el zombie.', [
                'pedido_id' => $pedido->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
