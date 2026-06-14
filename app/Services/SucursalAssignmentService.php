<?php

namespace App\Services;

use App\Models\Sucursal;
use App\Models\Barrio;
use App\Models\SucursalBarrioTarifa;
use App\Enums\EstadoPedido;

class SucursalAssignmentService
{
    /**
     * Dado un barrio_id, determina la mejor sucursal para atender el pedido.
     *
     * Reglas de negocio (en orden de prioridad):
     *  1. La sucursal debe tener cobertura activa en el barrio (sucursal_barrio_tarifas.activo = true)
     *  2. La sucursal debe estar activa (sucursales.activo = true)
     *  3. La sucursal debe estar abierta en este momento (hora_apertura ≤ now ≤ hora_cierre)
     *  4. Si el barrio tiene coordenadas → priorizar por menor distancia física a la sede
     *     Si no tiene coordenadas → priorizar por menor cantidad de pedidos activos del día
     *  5. Si ninguna sucursal cumple → retornar null (sin cobertura)
     *
     * @return array{sucursal: Sucursal|null, costo_envio: float, tiempo_estimado: int, tiene_cobertura: bool, mensaje: string|null}
     */
    public function resolver(string $barrioId): array
    {
        $barrio = Barrio::find($barrioId);

        if (!$barrio) {
            return $this->sinCobertura('Barrio no encontrado.');
        }

        // 1. Obtener todas las tarifas activas para este barrio
        $tarifas = SucursalBarrioTarifa::with('sucursal')
            ->where('barrio_id', $barrioId)
            ->where('activo', true)
            ->get();

        if ($tarifas->isEmpty()) {
            return $this->sinCobertura();
        }

        // 2. Filtrar sucursales activas
        $tarifas = $tarifas->filter(fn($t) => $t->sucursal && $t->sucursal->activo);

        if ($tarifas->isEmpty()) {
            return $this->sinCobertura();
        }

        // 3. Filtrar sucursales abiertas en este momento
        $tarifasAbiertas = $tarifas->filter(fn($t) => $t->sucursal->estaAbierta());

        // Si ninguna está abierta, no hay cobertura en este momento
        if ($tarifasAbiertas->isEmpty()) {
            return $this->sinCobertura('No hay sedes abiertas con cobertura en tu barrio en este momento.');
        }

        // 4a. Si el barrio tiene coordenadas → ordenar por distancia
        if ($barrio->latitud && $barrio->longitud) {
            $mejor = $tarifasAbiertas
                ->sortBy(fn($t) => $this->distanciaKm(
                    (float) $barrio->latitud,
                    (float) $barrio->longitud,
                    (float) $t->sucursal->latitud,
                    (float) $t->sucursal->longitud,
                ))
                ->first();
        } else {
            // 4b. Sin coordenadas → ordenar por pedidos activos (menor carga)
            $mejor = $tarifasAbiertas
                ->sortBy(fn ($t) => $t->sucursal->pedidos()
                    ->whereNotIn('estado', [
                        EstadoPedido::ENTREGADO->value,
                        EstadoPedido::CANCELADO->value,
                    ])
                    ->count()
                )
                ->first();
        }

        if (!$mejor) {
            return $this->sinCobertura();
        }

        return [
            'tiene_cobertura' => true,
            'sucursal'        => $mejor->sucursal,
            'costo_envio'     => (float) $mejor->costo_envio,
            'tiempo_estimado' => (int) $mejor->tiempo_estimado,
            'mensaje'         => null,
        ];
    }

    /**
     * Devuelve el costo de envío para una combinación específica Sede + Barrio.
     * Retorna 0 si no existe la tarifa (caso de seguridad).
     */
    public function obtenerTarifa(string $sucursalId, string $barrioId): array
    {
        $tarifa = SucursalBarrioTarifa::where('sucursal_id', $sucursalId)
            ->where('barrio_id', $barrioId)
            ->where('activo', true)
            ->first();

        return [
            'costo_envio'     => $tarifa ? (float) $tarifa->costo_envio : 0.00,
            'tiempo_estimado' => $tarifa ? (int) $tarifa->tiempo_estimado : 30,
        ];
    }

    /**
     * Calcula la distancia en kilómetros entre dos coordenadas usando la fórmula Haversine.
     */
    private function distanciaKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Si las coordenadas de la sede no están disponibles, asignar distancia infinita
        if (!$lat2 || !$lon2) {
            return PHP_FLOAT_MAX;
        }

        $radioTierra = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radioTierra * $c;
    }

    /**
     * Helper para retornar una respuesta de sin cobertura.
     */
    private function sinCobertura(?string $mensaje = null): array
    {
        return [
            'tiene_cobertura' => false,
            'sucursal'        => null,
            'costo_envio'     => 0.00,
            'tiempo_estimado' => 0,
            'mensaje'         => $mensaje ?? 'Lo sentimos, no tenemos cobertura de domicilio en tu barrio en este momento.',
        ];
    }
}
