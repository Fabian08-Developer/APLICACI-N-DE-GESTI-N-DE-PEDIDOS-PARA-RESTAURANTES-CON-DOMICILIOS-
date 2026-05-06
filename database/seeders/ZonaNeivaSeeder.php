<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ZonaCobertura;
use App\Models\Barrio;

class ZonaNeivaSeeder extends Seeder
{
    public function run(): void
    {
        $zonas = [
            [
                'nombre' => 'Zona Norte',
                'descripcion' => 'Sector Cándido y alrededores',
                'costo_envio' => 3000,
                'tiempo_estimado' => 25,
                'barrios' => ['Cándido Leguízamo', 'Eduardo Santos', 'Santa Inés', 'Camilo Torres', 'Villa del Prado']
            ],
            [
                'nombre' => 'Zona Oriente',
                'descripcion' => 'Sector Las Palmas e Ipanema',
                'costo_envio' => 4000,
                'tiempo_estimado' => 30,
                'barrios' => ['Las Palmas', 'Ipanema', 'Buganviles', 'Los Guaduales', 'La Rioja', 'El Tesoro']
            ],
            [
                'nombre' => 'Zona Sur',
                'descripcion' => 'Sector Timanco y Canaima',
                'costo_envio' => 4500,
                'tiempo_estimado' => 35,
                'barrios' => ['Timanco', 'Canaima', 'Limonar', 'Arismendi Mora', 'Puertas del Sol']
            ],
            [
                'nombre' => 'Zona Centro',
                'descripcion' => 'Casco urbano central y comercial',
                'costo_envio' => 2500,
                'tiempo_estimado' => 15,
                'barrios' => ['Centro', 'Altico', 'San Pedro', 'Los Mártires', 'Quirinal']
            ],
            [
                'nombre' => 'Zona Occidente',
                'descripcion' => 'Sector Galindo y Chicalá',
                'costo_envio' => 3500,
                'tiempo_estimado' => 25,
                'barrios' => ['Galindo', 'Chicalá', 'Santa Isabel', 'Villa Regina']
            ],
        ];

        foreach ($zonas as $z) {
            $barrios = $z['barrios'];
            unset($z['barrios']);
            
            $nuevaZona = ZonaCobertura::create($z);
            
            foreach ($barrios as $nombreBarrio) {
                $nuevaZona->barrios()->create(['nombre' => $nombreBarrio]);
            }
        }
    }
}
