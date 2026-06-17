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

        return view('cliente.empresa-home', compact('empresa', 'sucursales', 'apariencia'));
    }
}
