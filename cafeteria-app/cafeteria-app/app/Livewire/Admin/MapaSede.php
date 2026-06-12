<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Sucursal;
use App\Models\Barrio;

class MapaSede extends Component
{
    /**
     * Retorna los datos del mapa para el administrador.
     * Scope: SOLO la sucursal asignada al administrador.
     */
    public function getSucursalDataProperty(): ?array
    {
        $user = auth()->user();
        if (!$user->sucursal_id) {
            return null;
        }

        $sucursal = Sucursal::with(['barrios' => function ($q) {
                $q->where('barrios.activo', true)
                  ->whereNotNull('barrios.latitud')
                  ->whereNotNull('barrios.longitud');
            }])
            ->find($user->sucursal_id);

        if (!$sucursal) {
            return null;
        }

        return [
            'id'        => $sucursal->id,
            'nombre'    => $sucursal->nombre,
            'direccion' => $sucursal->direccion ?? 'Sin dirección',
            'telefono'  => $sucursal->telefono ?? '—',
            'activo'    => $sucursal->activo,
            'color'     => '#3b82f6',
            'latitud'   => $sucursal->latitud ? (float) $sucursal->latitud : null,
            'longitud'  => $sucursal->longitud ? (float) $sucursal->longitud : null,
            'barrios'   => $sucursal->barrios->map(fn($b) => [
                'id'       => $b->id,
                'nombre'   => $b->nombre,
                'latitud'  => (float) $b->latitud,
                'longitud' => (float) $b->longitud,
            ])->values()->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.mapa-sede', [
            'sucursalData' => $this->sucursalData,
        ])->layout('layouts.admin');
    }
}
