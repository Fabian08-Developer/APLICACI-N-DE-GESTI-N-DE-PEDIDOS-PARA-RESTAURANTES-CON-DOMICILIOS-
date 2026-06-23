<?php

namespace App\Livewire\Admin\Reportes;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Categoria;
use App\Models\Producto;
use App\Exports\ReporteVentasExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ManageReportes extends Component
{
    public $period = 'mes';
    public $start = '';
    public $end = '';
    public $categorias = [];
    public $metodos_pago = [];
    public $productos_top = [];

    protected $queryString = [
        'period' => ['except' => 'mes'],
        'start' => ['except' => ''],
        'end' => ['except' => ''],
        'categorias' => ['except' => []],
        'metodos_pago' => ['except' => []],
        'productos_top' => ['except' => []],
    ];

    public function mount()
    {
        if (!auth()->user()->sucursal_id) {
            return redirect()->route('sucursales');
        }

        // Intercept export requests
        if (request()->has('format')) {
            $format = request()->get('format');
            if ($format === 'pdf') {
                return $this->exportPdf();
            } elseif ($format === 'excel' || $format === 'csv') {
                return $this->exportExcel($format);
            }
        }

        // Initialize values from query parameters if present
        $this->period = request('period', 'mes');
        $this->start = request('start', '');
        $this->end = request('end', '');
        $this->categorias = request('categorias', []);
        $this->metodos_pago = request('metodos_pago', []);
        $this->productos_top = request('productos_top', []);
    }

    public function resetFilters()
    {
        $this->period = 'mes';
        $this->start = '';
        $this->end = '';
        $this->categorias = [];
        $this->metodos_pago = [];
        $this->productos_top = [];
    }

    /**
     * Centralized report data generation that applies all active filters.
     * OPTIMIZADO: cacheado 2 minutos por combinación de filtros.
     */
    private function obtenerDatosReporte()
    {
        $user        = auth()->user();
        $sucursal_id = $user->sucursal_id;

        // Clave de caché única por sucursal + combinación de filtros activos
        $cacheKey = "reporte_v2_{$sucursal_id}_{$this->period}_{$this->start}_{$this->end}_"
            . md5(implode(',', $this->categorias ?? []))
            . '_' . md5(implode(',', $this->metodos_pago ?? []))
            . '_' . md5(implode(',', $this->productos_top ?? []));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function () use ($user, $sucursal_id) {
            return $this->calcularDatosReporte($user, $sucursal_id);
        });
    }

    /**
     * Lógica interna de generación de datos del reporte (sin caché).
     */
    private function calcularDatosReporte($user, $sucursal_id)
    {
        // Parse and calculate Date range based on selected Period
        $period = $this->period ?: request('period', 'mes');
        $startVal = $this->start ?: request('start');
        $endVal = $this->end ?: request('end');
        
        switch ($period) {
            case 'hoy':
                $start = Carbon::today()->startOfDay();
                $end = Carbon::today()->endOfDay();
                break;
            case 'semana':
                $start = Carbon::today()->startOfWeek();
                $end = Carbon::today()->endOfWeek();
                break;
            case 'mes':
                $start = Carbon::today()->startOfMonth();
                $end = Carbon::today()->endOfDay();
                break;
            case 'personalizado':
                $start = $startVal ? Carbon::parse($startVal)->startOfDay() : Carbon::today()->startOfMonth();
                $end = $endVal ? Carbon::parse($endVal)->endOfDay() : Carbon::today()->endOfDay();
                break;
            default:
                $start = Carbon::today()->startOfMonth();
                $end = Carbon::today()->endOfDay();
                break;
        }

        // Retrieve filter requests
        $selectedCategorias = !empty($this->categorias) ? $this->categorias : request('categorias', []);
        $selectedMetodosPago = !empty($this->metodos_pago) ? $this->metodos_pago : request('metodos_pago', []);
        $selectedProductosTop = !empty($this->productos_top) ? $this->productos_top : request('productos_top', []);

        // Dynamic products top list based on actual sales count
        $productosTopFiltro = DetallePedido::where('sucursal_id', $sucursal_id)
            ->whereHas('pedido', function($q) {
                $q->where('estado', '!=', 'cancelado');
            })
            ->select('nombre_producto', DB::raw('SUM(cantidad) as total_cantidad'))
            ->groupBy('nombre_producto')
            ->orderByDesc('total_cantidad')
            ->take(10)
            ->pluck('nombre_producto')
            ->toArray();
            
        if (empty($productosTopFiltro)) {
            $productosTopFiltro = Producto::where('sucursal_id', $sucursal_id)
                ->take(10)
                ->pluck('nombre')
                ->toArray();
        }

        // Check if there are product or category specific filters
        $hasProductFilters = !empty($selectedCategorias) || !empty($selectedProductosTop);

        // Subquery for details matching sucursal, date range, and payment method
        $detallesQuery = DetallePedido::whereHas('pedido', function($q) use ($sucursal_id, $start, $end, $selectedMetodosPago) {
            $q->where('sucursal_id', $sucursal_id)
              ->whereBetween('creado_en', [$start, $end])
              ->where('estado', '!=', 'cancelado');
            if (!empty($selectedMetodosPago)) {
                $q->whereIn(DB::raw('lower(metodo_pago)'), array_map('strtolower', $selectedMetodosPago));
            }
        });

        if (!empty($selectedCategorias)) {
            $detallesQuery->whereHas('producto', function($q) use ($selectedCategorias) {
                $q->whereIn('categoria_id', $selectedCategorias);
            });
        }

        if (!empty($selectedProductosTop)) {
            $detallesQuery->whereIn('nombre_producto', $selectedProductosTop);
        }

        $detalles = $detallesQuery->with('pedido')->get();

        // Query orders matching filters for base calculations
        $pedidosQuery = Pedido::where('sucursal_id', $sucursal_id)
            ->whereBetween('creado_en', [$start, $end])
            ->where('estado', '!=', 'cancelado');

        if (!empty($selectedMetodosPago)) {
            $pedidosQuery->whereIn(DB::raw('lower(metodo_pago)'), array_map('strtolower', $selectedMetodosPago));
        }

        if (!empty($selectedCategorias)) {
            $pedidosQuery->whereHas('detalles.producto', function($q) use ($selectedCategorias) {
                $q->whereIn('categoria_id', $selectedCategorias);
            });
        }

        if (!empty($selectedProductosTop)) {
            $pedidosQuery->whereHas('detalles', function($q) use ($selectedProductosTop) {
                $q->whereIn('nombre_producto', $selectedProductosTop);
            });
        }

        $pedidosActual = $pedidosQuery->get();

        // Calculate current KPIs
        if ($hasProductFilters) {
            $ventasTotales = (float) $detalles->sum('subtotal');
            $clientesAtendidos = $detalles->pluck('pedido.sesion_cliente_id')->unique()->filter()->count();
            $totalPedidos = $detalles->pluck('pedido_id')->unique()->count();
        } else {
            $ventasTotales = (float) $pedidosActual->sum('total');
            $clientesAtendidos = $pedidosActual->unique('sesion_cliente_id')->count();
            $totalPedidos = $pedidosActual->count();
        }
        $ticketPromedio = $totalPedidos > 0 ? $ventasTotales / $totalPedidos : 0;

        $currentMetrics = [
            'ventasTotales' => $ventasTotales,
            'clientesAtendidos' => $clientesAtendidos,
            'ticketPromedio' => $ticketPromedio,
            'totalPedidos' => $totalPedidos,
        ];

        // Previous Metrics (for growth percentage calculations)
        $diff = $start->diffInDays($end) + 1;
        $startPrev = (clone $start)->subDays($diff);
        $endPrev = (clone $end)->subDays($diff);

        // Previous details
        $detallesPrevQuery = DetallePedido::whereHas('pedido', function($q) use ($sucursal_id, $startPrev, $endPrev, $selectedMetodosPago) {
            $q->where('sucursal_id', $sucursal_id)
              ->whereBetween('creado_en', [$startPrev, $endPrev])
              ->where('estado', '!=', 'cancelado');
            if (!empty($selectedMetodosPago)) {
                $q->whereIn(DB::raw('lower(metodo_pago)'), array_map('strtolower', $selectedMetodosPago));
            }
        });

        if (!empty($selectedCategorias)) {
            $detallesPrevQuery->whereHas('producto', function($q) use ($selectedCategorias) {
                $q->whereIn('categoria_id', $selectedCategorias);
            });
        }

        if (!empty($selectedProductosTop)) {
            $detallesPrevQuery->whereIn('nombre_producto', $selectedProductosTop);
        }

        $detallesPrev = $detallesPrevQuery->with('pedido')->get();

        // Previous orders
        $pedidosPrevQuery = Pedido::where('sucursal_id', $sucursal_id)
            ->whereBetween('creado_en', [$startPrev, $endPrev])
            ->where('estado', '!=', 'cancelado');

        if (!empty($selectedMetodosPago)) {
            $pedidosPrevQuery->whereIn(DB::raw('lower(metodo_pago)'), array_map('strtolower', $selectedMetodosPago));
        }

        if (!empty($selectedCategorias)) {
            $pedidosPrevQuery->whereHas('detalles.producto', function($q) use ($selectedCategorias) {
                $q->whereIn('categoria_id', $selectedCategorias);
            });
        }

        if (!empty($selectedProductosTop)) {
            $pedidosPrevQuery->whereHas('detalles', function($q) use ($selectedProductosTop) {
                $q->whereIn('nombre_producto', $selectedProductosTop);
            });
        }

        $pedidosPrev = $pedidosPrevQuery->get();

        if ($hasProductFilters) {
            $ventasPrev = (float) $detallesPrev->sum('subtotal');
            $clientesPrev = $detallesPrev->pluck('pedido.sesion_cliente_id')->unique()->filter()->count();
            $pedidosPrevCount = $detallesPrev->pluck('pedido_id')->unique()->count();
        } else {
            $ventasPrev = (float) $pedidosPrev->sum('total');
            $clientesPrev = $pedidosPrev->unique('sesion_cliente_id')->count();
            $pedidosPrevCount = $pedidosPrev->count();
        }
        $ticketPrev = $pedidosPrevCount > 0 ? $ventasPrev / $pedidosPrevCount : 0;

        $calculateChange = function($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 1);
        };

        $changes = [
            'ventasTotales' => $calculateChange($ventasTotales, $ventasPrev),
            'clientesAtendidos' => $calculateChange($clientesAtendidos, $clientesPrev),
            'ticketPromedio' => $calculateChange($ticketPromedio, $ticketPrev),
            'totalPedidos' => $calculateChange($totalPedidos, $pedidosPrevCount),
        ];

        // Categories Chart breakdown
        $categoriasChartQuery = Categoria::where('sucursal_id', $sucursal_id);
        if (!empty($selectedCategorias)) {
            $categoriasChartQuery->whereIn('id', $selectedCategorias);
        }
        $categoriasChart = $categoriasChartQuery->get();

        // Single query to fetch all category subtotals
        $categorySubtotalsQuery = DetallePedido::whereHas('pedido', function($q) use ($start, $end, $selectedMetodosPago) {
                $q->whereBetween('creado_en', [$start, $end])
                  ->where('estado', '!=', 'cancelado');
                if (!empty($selectedMetodosPago)) {
                    $q->whereIn(DB::raw('lower(metodo_pago)'), array_map('strtolower', $selectedMetodosPago));
                }
            })
            ->join('productos', 'detalle_pedido.producto_id', '=', 'productos.id')
            ->select('productos.categoria_id', DB::raw('SUM(detalle_pedido.subtotal) as total_subtotal'));

        if (!empty($selectedProductosTop)) {
            $categorySubtotalsQuery->whereIn('detalle_pedido.nombre_producto', $selectedProductosTop);
        }

        $categorySubtotals = $categorySubtotalsQuery->groupBy('productos.categoria_id')
            ->pluck('total_subtotal', 'productos.categoria_id')
            ->toArray();

        $categoriasChart = $categoriasChart->map(function($cat) use ($categorySubtotals) {
            $cat->total = (float) ($categorySubtotals[$cat->id] ?? 0.0);
            return $cat;
        });

        // Filter parameters
        $categoriasFiltro = Categoria::where('sucursal_id', $sucursal_id)->get();
        $metodosPagoFiltro = ['EFECTIVO', 'NEQUI', 'DAVIPLATA', 'TARJETA'];

        // Trend calculation (Optimized: Memory filtering to avoid O(N) database queries)
        $trendDates = [];
        $trendTotals = [];

        if ($start->isSameDay($end)) {
            // Group by hour for single-day reports
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = (clone $start)->hour($hour)->minute(0)->second(0);
                $hourEnd = (clone $start)->hour($hour)->minute(59)->second(59);
                
                $trendDates[] = $hourStart->format('H:00');
                
                if ($hasProductFilters) {
                    $hourSubtotal = $detalles->filter(function($detalle) use ($hourStart, $hourEnd) {
                        return $detalle->pedido && $detalle->pedido->creado_en->between($hourStart, $hourEnd);
                    })->sum('subtotal');
                    $trendTotals[] = (float) $hourSubtotal;
                } else {
                    $hourTotal = $pedidosActual->filter(function($pedido) use ($hourStart, $hourEnd) {
                        return $pedido->creado_en->between($hourStart, $hourEnd);
                    })->sum('total');
                    $trendTotals[] = (float) $hourTotal;
                }
            }
        } else {
            // Group by day for multi-day reports
            $loopStart = clone $start;
            $loopEnd = clone $end;
            if ($start->diffInDays($end) > 60) {
                $loopStart = (clone $end)->subDays(60);
            }
            
            while ($loopStart->lte($loopEnd)) {
                $dayStart = (clone $loopStart)->startOfDay();
                $dayEnd = (clone $loopStart)->endOfDay();
                
                $trendDates[] = $dayStart->format('d M');
                
                if ($hasProductFilters) {
                    $daySubtotal = $detalles->filter(function($detalle) use ($dayStart, $dayEnd) {
                        return $detalle->pedido && $detalle->pedido->creado_en->between($dayStart, $dayEnd);
                    })->sum('subtotal');
                    $trendTotals[] = (float) $daySubtotal;
                } else {
                    $dayTotal = $pedidosActual->filter(function($pedido) use ($dayStart, $dayEnd) {
                        return $pedido->creado_en->between($dayStart, $dayEnd);
                    })->sum('total');
                    $trendTotals[] = (float) $dayTotal;
                }
                
                $loopStart->addDay();
            }
        }

        return [
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
            'period' => $period,
            'changes' => $changes,
            'currentMetrics' => $currentMetrics,
            'categoriasChart' => $categoriasChart,
            'categoriasFiltro' => $categoriasFiltro,
            'metodosPagoFiltro' => $metodosPagoFiltro,
            'productosTopFiltro' => $productosTopFiltro,
            'trendTotals' => $trendTotals,
            'trendDates' => $trendDates,
            'catTotals' => $categoriasChart->pluck('total'),
            'catNames' => $categoriasChart->pluck('nombre'),
            'sucursal' => $user->sucursal,
            'selectedCategorias' => $selectedCategorias,
            'selectedMetodosPago' => $selectedMetodosPago,
            'selectedProductosTop' => $selectedProductosTop,
        ];
    }

    /**
     * Downloads sales report in PDF format.
     */
    public function exportPdf()
    {
        $data = $this->obtenerDatosReporte();
        $sections = request('sections', ['kpis', 'chart', 'categories']);
        $data['sections'] = $sections;

        $pdf = Pdf::loadView('admin.reportes.pdf', $data);
        return $pdf->download('Reporte_Ventas_' . $data['start']->format('Ymd') . '_' . $data['end']->format('Ymd') . '.pdf');
    }

    /**
     * Downloads sales report in Excel or CSV format.
     */
    public function exportExcel($format)
    {
        $data = $this->obtenerDatosReporte();
        $sections = request('sections', ['kpis', 'chart', 'categories']);
        $data['sections'] = $sections;

        $export = new ReporteVentasExport($data);
        $fileName = 'Reporte_Ventas_' . $data['start']->format('Ymd') . '_' . $data['end']->format('Ymd');
        
        if ($format === 'excel') {
            return Excel::download($export, $fileName . '.xlsx');
        } else {
            return Excel::download($export, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
    }

    public function render()
    {
        $reportData = $this->obtenerDatosReporte();

        $this->dispatch('reporte-actualizado', [
            'trendDates' => $reportData['trendDates'],
            'trendTotals' => $reportData['trendTotals'],
            'catNames' => $reportData['catNames'],
            'catTotals' => $reportData['catTotals'],
            'start' => Carbon::parse($reportData['start'])->format('Y-m-d'),
            'end' => Carbon::parse($reportData['end'])->format('Y-m-d'),
        ]);

        return view('livewire.admin.reportes.manage-reportes', [
            'start' => Carbon::parse($reportData['start'])->format('Y-m-d'),
            'end' => Carbon::parse($reportData['end'])->format('Y-m-d'),
            'period' => $reportData['period'],
            'changes' => $reportData['changes'],
            'currentMetrics' => $reportData['currentMetrics'],
            'categoriasChart' => $reportData['categoriasChart'],
            'categoriasFiltro' => $reportData['categoriasFiltro'],
            'metodosPagoFiltro' => $reportData['metodosPagoFiltro'],
            'productosTopFiltro' => $reportData['productosTopFiltro'],
            'trendTotals' => $reportData['trendTotals'],
            'trendDates' => $reportData['trendDates'],
            'catTotals' => $reportData['catTotals'],
            'catNames' => $reportData['catNames'],
        ])->layout('layouts.admin');
    }
}
