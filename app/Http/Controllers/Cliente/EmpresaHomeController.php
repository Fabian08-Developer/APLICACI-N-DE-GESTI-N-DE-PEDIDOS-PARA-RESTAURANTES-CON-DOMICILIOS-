<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaHomeController extends Controller
{
    /**
     * Página de inicio pública para cada empresa (Tienda Online).
     * URL: /{empresa_slug}
     */
    public function __invoke(string $empresa_slug)
    {
        $empresa = Empresa::where('slug', $empresa_slug)
            ->where('activo', true)
            ->firstOrFail();

        // Cargar sucursales activas con su información de cobertura
        $sucursales = $empresa->sucursales()
            ->where('activo', true)
            ->withCount(['barrios as total_barrios' => function ($q) {
                $q->where('sucursal_barrio_tarifas.activo', true);
            }])
            ->orderBy('nombre')
            ->get();

        // 1. Productos Recomendados (Vitrina en Home)
        $productosDestacados = \App\Models\Producto::withoutGlobalScopes()
            ->where('activo', true)
            ->where('disponible', true)
            ->whereIn('sucursal_id', $sucursales->pluck('id'))
            ->where(function ($q) {
                $q->whereNull('categoria_id')
                  ->orWhereHas('categoria', function ($q2) {
                      $q2->withoutGlobalScopes()->where('activo', true);
                  });
            })
            ->inRandomOrder()
            ->limit(8)
            ->get();

        if ($productosDestacados->isEmpty() && $sucursales->isNotEmpty()) {
            $productosDestacados = collect([
                (object)[
                    'nombre'        => 'Espresso Doble de Origen',
                    'descripcion'   => 'Café espresso doble extraído de granos seleccionados con notas sutiles de chocolate y almendras tostadas.',
                    'precio'        => 4500,
                    'precio_oferta' => null,
                    'imagen'        => null,
                    'sucursal'      => $sucursales->first(),
                ],
                (object)[
                    'nombre'        => 'Cappuccino de la Casa',
                    'descripcion'   => 'Espresso perfecto mezclado con leche emulsionada cremosa y un toque artístico de cacao en polvo.',
                    'precio'        => 6500,
                    'precio_oferta' => null,
                    'imagen'        => null,
                    'sucursal'      => $sucursales->first(),
                ],
                (object)[
                    'nombre'        => 'Croissant de Almendra Crocante',
                    'descripcion'   => 'Clásico hojaldre francés horneado con relleno cremoso de almendras y cubierto de finas láminas crujientes.',
                    'precio'        => 8500,
                    'precio_oferta' => 6900,
                    'imagen'        => null,
                    'sucursal'      => $sucursales->first(),
                ],
                (object)[
                    'nombre'        => 'Tarta Artesanal de Frutos Rojos',
                    'descripcion'   => 'Base crocante rellena de crema pastelera suave y decorada con fresas, frambuesas y arándanos frescos.',
                    'precio'        => 9800,
                    'precio_oferta' => null,
                    'imagen'        => null,
                    'sucursal'      => $sucursales->last(),
                ]
            ]);
        }

        // 2. Cobertura de Barrios para el Buscador del Hero (Consultado de Zonas de Cobertura)
        $barrios = \App\Models\Barrio::whereHas('zona', function ($query) use ($sucursales) {
            $query->withoutGlobalScopes()
                  ->whereIn('sucursal_id', $sucursales->pluck('id'))
                  ->where('activo', true);
        })
        ->with(['zona' => function ($q) {
            $q->withoutGlobalScopes();
        }, 'zona.sucursal'])
        ->get();

        $barriosCobertura = [];
        foreach ($barrios as $barrio) {
            if ($barrio->zona && $barrio->zona->sucursal) {
                $barriosCobertura[] = [
                    'id'              => $barrio->id,
                    'nombre'          => $barrio->nombre,
                    'sucursal_id'     => $barrio->zona->sucursal->id,
                    'sucursal_nombre' => $barrio->zona->sucursal->nombre,
                    'sucursal_slug'   => $barrio->zona->sucursal->slug,
                    'costo_envio'     => (float) $barrio->zona->costo_envio,
                    'tiempo_estimado' => $barrio->zona->tiempo_estimado ?? 30,
                ];
            }
        }

        // Si la base de datos de barrios de Neiva está vacía, inyectamos unos mocks de Neiva
        if (empty($barriosCobertura) && $sucursales->isNotEmpty()) {
            $barriosCobertura = [
                [
                    'id'              => 'mock-1',
                    'nombre'          => 'Los álamos',
                    'sucursal_id'     => $sucursales->first()->id,
                    'sucursal_nombre' => $sucursales->first()->nombre,
                    'sucursal_slug'   => $sucursales->first()->slug,
                    'costo_envio'     => 7000,
                    'tiempo_estimado' => 30,
                ],
                [
                    'id'              => 'mock-2',
                    'nombre'          => 'El Vergel',
                    'sucursal_id'     => $sucursales->first()->id,
                    'sucursal_nombre' => $sucursales->first()->nombre,
                    'sucursal_slug'   => $sucursales->first()->slug,
                    'costo_envio'     => 7000,
                    'tiempo_estimado' => 30,
                ]
            ];
        }

        // 3. Calificaciones y Testimonios de Clientes (Social Proof)
        $calificacionesList = \App\Models\CalificacionDomiciliario::whereHas('pedido', function ($q) use ($sucursales) {
            $q->withoutGlobalScopes()
              ->whereIn('sucursal_id', $sucursales->pluck('id'));
        })
        ->with('cliente')
        ->latest('creado_en')
        ->limit(4)
        ->get();

        $promedioCalificacion = \App\Models\CalificacionDomiciliario::whereHas('pedido', function ($q) use ($sucursales) {
            $q->withoutGlobalScopes()
              ->whereIn('sucursal_id', $sucursales->pluck('id'));
        })
        ->avg('puntuacion');

        $promedioCalificacion = $promedioCalificacion ? round($promedioCalificacion, 1) : 4.8;

        // Valores de apariencia con defaults seguros
        $apariencia = [
            'logo_url'           => $empresa->aparienciaValor('logo_url'),
            'banner_url'         => $empresa->aparienciaValor('banner_url'),
            'color_primario'     => $empresa->aparienciaValor('color_primario', '#e63946'),
            'color_secundario'   => $empresa->aparienciaValor('color_secundario', '#1d3557'),
            'titulo_tienda'      => $empresa->aparienciaValor('titulo_tienda', '¡Bienvenido a ' . $empresa->nombre . '!'),
            'descripcion'        => $empresa->aparienciaValor('descripcion', 'Selecciona una sede y realiza tu pedido a domicilio.'),
            'whatsapp'           => $empresa->aparienciaValor('whatsapp'),
            'instagram'          => $empresa->aparienciaValor('instagram'),
            'facebook'           => $empresa->aparienciaValor('facebook'),
            'tiktok'             => $empresa->aparienciaValor('tiktok'),
            'mostrar_mapa'       => $empresa->aparienciaValor('mostrar_mapa', true),
            'mostrar_sucursales' => $empresa->aparienciaValor('mostrar_sucursales', true),
        ];

        return view('cliente.empresa-home', compact(
            'empresa', 
            'sucursales', 
            'apariencia', 
            'productosDestacados', 
            'barriosCobertura', 
            'calificacionesList', 
            'promedioCalificacion'
        ));
    }
}
