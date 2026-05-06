<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Usuario;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard principal del administrador
     * Ruta: GET /admin/dashboard
     */
    public function index()
    {
        // ── Tarjetas de resumen ──
        $totalUsuarios   = Usuario::where('estado', true)->count();
        $totalCategorias = Categoria::count();
        $totalMesas      = Mesa::count();

        // Pedidos de hoy
        $pedidosHoy = Pedido::whereDate('created_at', today())->count();

        // Pedidos activos (en cocina o creados)
        $pedidosActivos = Pedido::whereIn('estado', ['CREADO', 'EN_COCINA'])->count();

        // Mesas disponibles vs ocupadas
        $mesasDisponibles = Mesa::where('estado', 'DISPONIBLE')->count();
        $mesasOcupadas    = Mesa::where('estado', 'OCUPADA')->count();

        // Últimos 5 pedidos
        $ultimosPedidos = Pedido::with(['SesionMesa.mesa', 'mesero'])
                                ->latest()
                                ->take(5)
                                ->get();

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'totalCategorias',
            'totalMesas',
            'pedidosHoy',
            'pedidosActivos',
            'mesasDisponibles',
            'mesasOcupadas',
            'ultimosPedidos',
        ));
    }
}