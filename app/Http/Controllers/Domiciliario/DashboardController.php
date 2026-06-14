<?php

namespace App\Http\Controllers\Domiciliario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Mock data for the view
        $domiciliario = [
            'nombre' => 'Carlos Andrés Tovar',
            'codigo' => 'DOM001',
            'estado' => 'Disponible',
            'calificacion' => 4.8,
            'vehiculo' => 'Moto',
            'placa' => 'HYR-45D',
            'zona' => 'Centro - Altico',
            'estadisticas' => [
                'mes' => [
                    'entregas' => 156,
                    'ganancias' => 580000,
                    'tiempo_promedio' => '12 min',
                    'efectividad' => '98%'
                ],
                'dia' => [
                    'entregas' => 8,
                    'ganancias' => 45000,
                    'pendientes' => 3,
                    'km_recorrer' => 6.8,
                    'por_cobrar' => 88000
                ]
            ]
        ];

        $pedidos = [
            [
                'id' => 'PED-2024-089',
                'hora_asignacion' => '10:30 AM',
                'estado' => 'En Camino',
                'cliente' => [
                    'nombre' => 'María García',
                    'direccion' => 'Cra 5 #10-45, Barrio Centro',
                    'referencia' => 'Edificio Bancolombia, Apto 302',
                    'telefono' => '3001234567'
                ],
                'productos' => [
                    ['cantidad' => 2, 'nombre' => 'Café Especial del Huila', 'precio' => 6000],
                    ['cantidad' => 4, 'nombre' => 'Achira Huilense', 'precio' => 8000]
                ],
                'notas' => 'Llamar al llegar',
                'tiempo_estimado' => '10 min',
                'distancia' => '1.2 km',
                'total' => 24000,
                'metodo_pago' => 'Efectivo'
            ],
            [
                'id' => 'PED-2024-091',
                'hora_asignacion' => '10:45 AM',
                'estado' => 'Recogido',
                'cliente' => [
                    'nombre' => 'Juan Pérez',
                    'direccion' => 'Calle 8 #15-20, Las Palmas',
                    'referencia' => 'Casa esquinera verde, frente a la tienda',
                    'telefono' => '3109876543'
                ],
                'productos' => [
                    ['cantidad' => 1, 'nombre' => 'Desayuno Opita', 'precio' => 18000],
                    ['cantidad' => 1, 'nombre' => 'Jugo de Cholupa', 'precio' => 5000]
                ],
                'notas' => null,
                'tiempo_estimado' => '18 min',
                'distancia' => '2.5 km',
                'total' => 28000,
                'metodo_pago' => 'Transferencia'
            ],
            [
                'id' => 'PED-2024-093',
                'hora_asignacion' => '11:00 AM',
                'estado' => 'Asignado',
                'cliente' => [
                    'nombre' => 'Ana Rodríguez',
                    'direccion' => 'Cra 7 #5-30, Quirinal',
                    'referencia' => 'Al lado de la panadería Don Pan',
                    'telefono' => '3204567890'
                ],
                'productos' => [
                    ['cantidad' => 2, 'nombre' => 'Tamal Huilense', 'precio' => 16000],
                    ['cantidad' => 2, 'nombre' => 'Café Especial del Huila', 'precio' => 6000],
                    ['cantidad' => 6, 'nombre' => 'Bizcocho de Cuajada', 'precio' => 9000]
                ],
                'notas' => 'Cliente frecuente, pedir propina con amabilidad',
                'tiempo_estimado' => '25 min',
                'distancia' => '3.1 km',
                'total' => 36000,
                'metodo_pago' => 'Efectivo'
            ]
        ];

        $historial = [
            [
                'id' => 'PED-2024-085',
                'hora' => '9:15 AM',
                'cliente' => 'Pedro López',
                'total' => 15000,
                'propina' => 2000
            ],
            [
                'id' => 'PED-2024-082',
                'hora' => '8:45 AM',
                'cliente' => 'Laura Martínez',
                'total' => 28000,
                'propina' => 3000
            ],
            [
                'id' => 'PED-2024-080',
                'hora' => '8:20 AM',
                'cliente' => 'Diego Sánchez',
                'total' => 12000,
                'propina' => 0
            ]
        ];

        return view('domiciliario.dashboard', compact('domiciliario', 'pedidos', 'historial'));
    }
}
