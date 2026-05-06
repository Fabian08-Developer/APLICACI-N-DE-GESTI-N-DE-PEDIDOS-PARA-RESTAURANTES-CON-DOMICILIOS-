<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Categoria;
use App\Models\Pago;

class SalesReportController extends Controller
{
    /**
     * Muestra el Dashboard principal de Reportes de Ventas
     */
    public function index(Request $request)
    {
        // 1. Manejo de Fechas y Periodos
        $period = $request->input('period', 'mes');
        
        if ($period !== 'personalizado') {
            if ($period === 'hoy') {
                $start = Carbon::now()->format('Y-m-d');
                $end = $start;
            } elseif ($period === 'semana') {
                $start = Carbon::now()->startOfWeek()->format('Y-m-d');
                $end = Carbon::now()->endOfWeek()->format('Y-m-d');
            } else { // mes (default)
                $start = Carbon::now()->startOfMonth()->format('Y-m-d');
                $end = Carbon::now()->endOfMonth()->format('Y-m-d');
            }
        } else {
            $start = $request->input('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $end = $request->input('end', Carbon::now()->format('Y-m-d'));
        }

        // Calcular periodo anterior para sacar porcentajes (+10% vs periodo anterior equivalente)
        $daysDiff = Carbon::parse($start)->diffInDays(Carbon::parse($end));
        $prevStart = Carbon::parse($start)->subDays($daysDiff + 1)->format('Y-m-d');
        $prevEnd = Carbon::parse($start)->subDays(1)->format('Y-m-d');

        // 2. Cálculos de KPIs (Actual vs Anterior)
        $currentMetrics = $this->getMetrics($start, $end);
        $prevMetrics = $this->getMetrics($prevStart, $prevEnd);

        $changes = [
            'ventasTotales' => $this->calcChange($currentMetrics['ventasTotales'], $prevMetrics['ventasTotales']),
            'clientesAtendidos' => $this->calcChange($currentMetrics['clientesAtendidos'], $prevMetrics['clientesAtendidos']),
            'ticketPromedio' => $this->calcChange($currentMetrics['ticketPromedio'], $prevMetrics['ticketPromedio']),
            'totalPedidos' => $this->calcChange($currentMetrics['totalPedidos'], $prevMetrics['totalPedidos']),
        ];

        // 3. Gráfica de Tendencia (Ventas agrupadas por día para el Area Chart)
        $pedidosTendencia = Pedido::completado()
            ->rangoFechas($start, $end)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $trendDates = $pedidosTendencia->pluck('date');
        $trendTotals = $pedidosTendencia->pluck('total');

        // 4. Gráfica de Categorías (Donut Chart)
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

        $catNames = $categoriasChart->pluck('nombre');
        $catTotals = $categoriasChart->pluck('total');

        // 5. Datos dinámicos para los filtros
        $categoriasFiltro = Categoria::orderBy('nombre')->get();
        $metodosPagoFiltro = Pago::select('metodo_pago')->distinct()->whereNotNull('metodo_pago')->pluck('metodo_pago');
        $productosTopFiltro = DB::table('detalle_pedidos')
            ->join('productos', 'detalle_pedidos.producto_id', '=', 'productos.id')
            ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->where('pedidos.estado', 'ENTREGADO')
            ->select('productos.nombre', DB::raw('SUM(detalle_pedidos.cantidad) as total_vendido'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->pluck('productos.nombre');

        return view('admin.reports.sales', compact(
            'start', 'end', 'period',
            'currentMetrics', 'changes', 
            'trendDates', 'trendTotals',
            'catNames', 'catTotals', 'categoriasChart',
            'categoriasFiltro', 'metodosPagoFiltro', 'productosTopFiltro'
        ));
    }

    /**
     * Exporta el reporte en formato PDF, Excel o CSV
     */
    public function export(Request $request)
    {
        $period = $request->input('period', 'mes');
        $format = $request->input('format', 'pdf');

        if ($period !== 'personalizado') {
            if ($period === 'hoy') {
                $start = Carbon::now()->format('Y-m-d');
                $end = $start;
            } elseif ($period === 'semana') {
                $start = Carbon::now()->startOfWeek()->format('Y-m-d');
                $end = Carbon::now()->endOfWeek()->format('Y-m-d');
            } else { // mes
                $start = Carbon::now()->startOfMonth()->format('Y-m-d');
                $end = Carbon::now()->endOfMonth()->format('Y-m-d');
            }
        } else {
            $start = $request->input('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $end = $request->input('end', Carbon::now()->format('Y-m-d'));
        }

        if ($format === 'excel') {
            return Excel::download(new SalesExport($start, $end), "ventas_{$start}_a_{$end}.xlsx");
        }

        if ($format === 'csv') {
            return Excel::download(new SalesExport($start, $end), "ventas_{$start}_a_{$end}.csv", \Maatwebsite\Excel\Excel::CSV);
        }

        // ── Secciones seleccionadas ──────────────────────────────────
        // El sidebar envía sections[] con los slugs activos (kpis, chart, categories, products, comparison, predictions)
        // Si no se envía ninguna, incluimos todo por defecto.
        $sections = $request->input('sections', ['kpis', 'chart', 'categories', 'products', 'comparison', 'predictions']);
        if (!is_array($sections)) $sections = [$sections];

        // --- Lógica PDF ---
        // Recuperamos data para la vista PDF
        $currentMetrics = $this->getMetrics($start, $end);
        
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

        $pedidos = Pedido::completado()
            ->rangoFechas($start, $end)
            ->with(['mesero', 'detalles.producto'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Productos más vendidos (para sección "products")
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

        $pdf = Pdf::loadView('admin.reports.exports.sales_pdf', compact(
            'start', 'end', 'currentMetrics', 'categoriasChart', 'pedidos', 'productosTop', 'sections'
        ));

        return $pdf->download("reporte_ventas_{$start}_a_{$end}.pdf");

    }

    /**
     * Helper para obtener las métricas de un periodo
     */
    private function getMetrics($start, $end)
    {
        $pedidos = Pedido::completado()->rangoFechas($start, $end)->get();
        $ventasTotales = $pedidos->sum('total');
        // Para simplificar, 1 pedido completado equivale a 1 transacción de cliente
        $totalPedidos = $pedidos->count(); 
        $ticketPromedio = $totalPedidos > 0 ? $ventasTotales / $totalPedidos : 0;
        
        return [
            'ventasTotales' => $ventasTotales,
            'clientesAtendidos' => $totalPedidos, 
            'ticketPromedio' => $ticketPromedio,
            'totalPedidos' => $totalPedidos
        ];
    }

    /**
     * Helper para calcular el porcentaje de crecimiento/caída
     */
    private function calcChange($current, $prev)
    {
        if ($prev == 0) return $current > 0 ? 100 : 0;
        return round((($current - $prev) / $prev) * 100, 1);
    }
}
