<?php

namespace App\Livewire\Gerente;

use Livewire\Component;
use App\Models\Sucursal;
use App\Models\Barrio;

class MapaSedes extends Component
{
    /**
     * Retorna los datos de sucursales y barrios formateados para Leaflet.js.
     * Scope: TODAS las sucursales de la empresa del gerente.
     */
    public function getSucursalesDataProperty(): array
    {
        $empresaId = auth()->user()->empresa_id;

        // Paleta de colores para cada sede (máx. 10 sedes)
        $colores = [
            '#3b82f6', // azul
            '#10b981', // verde esmeralda
            '#f59e0b', // ámbar
            '#ef4444', // rojo
            '#8b5cf6', // violeta
            '#ec4899', // rosa
            '#14b8a6', // turquesa
            '#f97316', // naranja
            '#6366f1', // índigo
            '#84cc16', // lima
        ];

        $sucursales = Sucursal::where('empresa_id', $empresaId)
            ->with(['barrios' => function ($q) {
                $q->where('barrios.activo', true)
                  ->whereNotNull('barrios.latitud')
                  ->whereNotNull('barrios.longitud');
            }])
            ->get();

        return $sucursales->values()->map(function ($sucursal, $index) use ($colores) {
            $color = $colores[$index % count($colores)];

            return [
                'id'        => $sucursal->id,
                'nombre'    => $sucursal->nombre,
                'direccion' => $sucursal->direccion ?? 'Sin dirección',
                'telefono'  => $sucursal->telefono ?? '—',
                'activo'    => $sucursal->activo,
                'color'     => $color,
                'latitud'   => $sucursal->latitud ? (float) $sucursal->latitud : null,
                'longitud'  => $sucursal->longitud ? (float) $sucursal->longitud : null,
                'barrios'   => $sucursal->barrios->map(fn($b) => [
                    'id'      => $b->id,
                    'nombre'  => $b->nombre,
                    'latitud' => (float) $b->latitud,
                    'longitud'=> (float) $b->longitud,
                ])->values()->toArray(),
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.gerente.mapa-sedes', [
            'sucursalesData' => $this->sucursalesData,
        ])->layout('layouts.app');
    }
}
