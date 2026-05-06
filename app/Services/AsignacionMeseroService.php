<?php

namespace App\Services;

use App\Enums\EstadoPedido;
use App\Models\Usuario;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;

class AsignacionMeseroService
{
    /**
     * Estados de pedido que se consideran "abiertos" para el balance de carga.
     * Un mesero con más pedidos en estos estados se considera más ocupado.
     */
    private const ESTADOS_ACTIVOS = [
        'PENDIENTE_PAGO',
        'CREADO',
        'EN_COCINA',
        'EN_PREPARACION',
        'LISTO',
    ];

    /**
     * Devuelve el mesero asignado a la mesa (si ya hay uno) o el que tenga menos pedidos abiertos.
     *
     * @param int|null $mesaId  Mesa asociada al pedido (para mantener el mismo mesero)
     * @return Usuario|null     null si no hay meseros activos disponibles
     */
    public function obtenerMeseroDisponible(?int $mesaId = null): ?Usuario
    {
        // 1. Si se proporciona la mesa, verificamos si ya hay un mesero atendiéndola
        if ($mesaId) {
            // Buscamos cualquier pedido previo en las sesiones activas de esta mesa
            $pedidoExistente = Pedido::whereHas('sesionMesa', function ($q) use ($mesaId) {
                $q->where('mesa_id', $mesaId)
                  ->where('estado', \App\Models\SesionMesa::ESTADO_ACTIVA);
            })->orderBy('created_at', 'asc')->first();

            if ($pedidoExistente && $pedidoExistente->mesero_id) {
                // Verificar que el mesero asignado siga disponible/activo en el sistema
                $meseroAsignado = Usuario::where('id', $pedidoExistente->mesero_id)
                    ->where('estado', true)
                    ->first();
                    
                if ($meseroAsignado) {
                    Log::info('Mesero mantenido para la misma mesa', [
                        'mesero_id' => $meseroAsignado->id,
                        'mesa_id'   => $mesaId,
                    ]);
                    return $meseroAsignado;
                }
            }
        }

        // 2. Balance inicial: si no hay un mesero atendiendo la mesa, buscar el menos ocupado
        $meseros = Usuario::whereHas('rol', fn($q) => $q->where('nombre', 'mesero'))
            ->where('estado', true)
            ->get();

        if ($meseros->isEmpty()) {
            Log::warning('AsignacionMeseroService: no hay meseros activos disponibles.');
            return null;
        }

        // Contar pedidos activos por mesero en una sola consulta para evitar N+1
        $conteosPorMesero = Pedido::whereIn('mesero_id', $meseros->pluck('id'))
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->selectRaw('mesero_id, COUNT(*) as total')
            ->groupBy('mesero_id')
            ->pluck('total', 'mesero_id');

        // Asignar al mesero con menor carga (0 si no tiene pedidos activos)
        $meseroElegido = $meseros->sortBy(
            fn(Usuario $mesero) => $conteosPorMesero->get($mesero->id, 0)
        )->first();

        Log::info('Mesero balanceado', [
            'mesero_id'      => $meseroElegido->id,
            'pedidos_activos' => $conteosPorMesero->get($meseroElegido->id, 0),
        ]);

        return $meseroElegido;
    }
}