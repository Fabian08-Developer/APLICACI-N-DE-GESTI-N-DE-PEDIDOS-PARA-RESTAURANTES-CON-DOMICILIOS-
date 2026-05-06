<?php

namespace App\Services\Reports;

use App\Models\Pedido;
use App\Models\Categoria;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportDataService
{
    /**
     * Obtiene todas las métricas y datos necesarios para un reporte de ventas
     */
    public function getSalesSummary($start, $end)
    {
        $currentMetrics = $this->getMetrics($start, $end);
        
        // Gráfica de Categorías
        $categoriasChart = DB::table('detalle_pedidos')
            ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->join('productos', 'detalle_pedidos.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('pedidos.estado', 'ENTREGADO')
            ->whereBetween('pedidos.created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->select('categorias.nombre', DB::raw('SUM(detalle_pedidos.subtotal) as total'))
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderByDesc('total')
            ->get();

        // Pedidos detallados
        $pedidos = Pedido::completado()
            ->rangoFechas($start, $end)
            ->with(['mesero', 'detalles.producto'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Productos más vendidos
        $productosTop = DB::table('detalle_pedidos')
            ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->join('productos', 'detalle_pedidos.producto_id', '=', 'productos.id')
            ->where('pedidos.estado', 'ENTREGADO')
            ->whereBetween('pedidos.created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->select('productos.nombre', DB::raw('SUM(detalle_pedidos.cantidad) as cantidad'), DB::raw('SUM(detalle_pedidos.subtotal) as total'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'start' => $start,
            'end' => $end,
            'metrics' => $currentMetrics,
            'categories' => $categoriasChart,
            'orders' => $pedidos,
            'top_products' => $productosTop
        ];
    }

    /**
     * Helper para obtener las métricas base
     */
    public function getMetrics($start, $end)
    {
        $pedidos = Pedido::completado()->rangoFechas($start, $end)->get();
        $ventasTotales = $pedidos->sum('total');
        $totalPedidos = $pedidos->count(); 
        $ticketPromedio = $totalPedidos > 0 ? $ventasTotales / $totalPedidos : 0;
        
        return [
            'ventasTotales' => $ventasTotales,
            'clientesAtendidos' => $totalPedidos, 
            'ticketPromedio' => $ticketPromedio,
            'totalPedidos' => $totalPedidos
        ];
    }
}
