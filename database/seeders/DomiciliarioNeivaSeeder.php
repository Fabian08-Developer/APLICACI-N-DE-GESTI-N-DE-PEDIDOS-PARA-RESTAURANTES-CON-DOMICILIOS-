<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Domiciliario;
use App\Models\ZonaCobertura;

class DomiciliarioNeivaSeeder extends Seeder
{
    public function run(): void
    {
        $zonas = ZonaCobertura::all();

        if ($zonas->isEmpty()) return;

        $equipo = [
            [
                'nombre' => 'Juan Carlos Perdomo',
                'telefono' => '315 234 5678',
                'vehiculo_tipo' => 'moto',
                'placa' => 'XYZ-12A',
                'estado' => 'disponible',
                'zona_nombre' => 'Zona Norte'
            ],
            [
                'nombre' => 'Luis Alberto Rojas',
                'telefono' => '320 876 5432',
                'vehiculo_tipo' => 'moto',
                'placa' => 'ABC-34B',
                'estado' => 'en_ruta',
                'zona_nombre' => 'Zona Oriente'
            ],
            [
                'nombre' => 'Marta Lucía Castro',
                'telefono' => '311 456 7890',
                'vehiculo_tipo' => 'bicicleta',
                'placa' => null,
                'estado' => 'disponible',
                'zona_nombre' => 'Zona Centro'
            ],
            [
                'nombre' => 'Ricardo Silva',
                'telefono' => '300 123 9876',
                'vehiculo_tipo' => 'moto',
                'placa' => 'FGH-56C',
                'estado' => 'ocupado',
                'zona_nombre' => 'Zona Sur'
            ],
            [
                'nombre' => 'Andrés Felipe Cuenca',
                'telefono' => '318 555 4433',
                'vehiculo_tipo' => 'moto',
                'placa' => 'JKL-78D',
                'estado' => 'fuera_servicio',
                'zona_nombre' => 'Zona Occidente'
            ]
        ];

        foreach ($equipo as $d) {
            $zona = $zonas->where('nombre', $d['zona_nombre'])->first();
            
            if ($zona) {
                Domiciliario::create([
                    'nombre' => $d['nombre'],
                    'telefono' => $d['telefono'],
                    'vehiculo_tipo' => $d['vehiculo_tipo'],
                    'placa' => $d['placa'],
                    'estado' => $d['estado'],
                    'zona_id' => $zona->id,
                ]);
            }
        }
    }
}
