<?php

namespace App\Livewire\Gerente;

use Livewire\Component;
use App\Models\Pedido;
use App\Models\Sucursal;
use App\Models\DetallePedido;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GlobalReports extends Component
{
    public $sucursalFilter = 'all';
    public $startDate;
    public $endDate;

    protected $queryString = [
        'sucursalFilter' => ['except' => 'all'],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => '']
    ];

    public function mount()
    {
        // Verificar que el usuario tenga rol gerente
        if (!Auth::user()->hasRole('gerente')) {
            abort(403, 'Acceso denegado. Se requiere el rol de Gerente.');
        }

        // Inicializar fechas al mes actual
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    public function resetFilters()
    {
        $this->sucursalFilter = 'all';
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    /**
     * Obtiene el listado de todas las sucursales de la empresa para el selector de filtros
     */
    public function getSucursalesSelectorProperty()
    {
        $empresaId = Auth::user()->empresa_id;
        return Sucursal::where('empresa_id', $empresaId)->orderBy('nombre')->get();
    }

    /**
     * Obtiene las métricas generales (RF-G66)
     */
    public function getMetricsProperty()
    {
        $empresaId = Auth::user()->empresa_id;
        
        // Obtener IDs de las sucursales activas de la empresa
        $activeBranches = Sucursal::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->pluck('id');

        // Contar sucursales activas
        $sucursalesActivasCount = $activeBranches->count();

        // Determinar qué sucursales consultar para las ventas y pedidos
        if ($this->sucursalFilter && $this->sucursalFilter !== 'all') {
            $branchScope = [$this->sucursalFilter];
        } else {
            $branchScope = $activeBranches->toArray();
        }

        // Consultar pedidos en el rango de fechas
        $query = Pedido::whereIn('sucursal_id', $branchScope)
            ->where('estado', '!=', 'cancelado');

        if ($this->startDate) {
            $query->whereDate('creado_en', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('creado_en', '<=', $this->endDate);
        }

        $ventasTotales = $query->sum('total');
        $totalPedidos = $query->count();
        $ticketPromedio = $totalPedidos > 0 ? $ventasTotales / $totalPedidos : 0;

        return [
            'ventas_totales' => $ventasTotales,
            'total_pedidos' => $totalPedidos,
            'ticket_promedio' => $ticketPromedio,
            'sucursales_activas' => $sucursalesActivasCount,
        ];
    }

    /**
     * Obtiene los datos comparativos de las sucursales (RF-G67 y RF-G69)
     */
    public function getSucursalesDataProperty()
    {
        $empresaId = Auth::user()->empresa_id;

        // Consultar sucursales
        $sucursalesQuery = Sucursal::where('empresa_id', $empresaId);
        
        if ($this->sucursalFilter && $this->sucursalFilter !== 'all') {
            $sucursalesQuery->where('id', $this->sucursalFilter);
        }

        $sucursales = $sucursalesQuery->get();
        $totalSucursales = Sucursal::where('empresa_id', $empresaId)->where('activo', true)->count();

        $data = $sucursales->map(function ($sucursal) {
            // Ventas y pedidos por sucursal en el rango de fechas
            $query = Pedido::where('sucursal_id', $sucursal->id)
                ->where('estado', '!=', 'cancelado');

            if ($this->startDate) {
                $query->whereDate('creado_en', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $query->whereDate('creado_en', '<=', $this->endDate);
            }

            $ventas = $query->sum('total');
            $pedidos = $query->count();

            return [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre,
                'activo' => $sucursal->activo,
                'ventas' => (float) $ventas,
                'pedidos' => (int) $pedidos,
            ];
        });

        // Suma total de ventas de todas las filas mostradas para calcular el porcentaje
        $totalVentasMostradas = $data->sum('ventas');

        // Calcular porcentajes y ordenar
        return $data->map(function ($item) use ($totalVentasMostradas, $totalSucursales) {
            // % de ventas para RF-G67
            $item['porcentaje_ventas'] = $totalVentasMostradas > 0 ? ($item['ventas'] / $totalVentasMostradas) * 100 : 0;
            
            // % dividido entre la cantidad de sucursales para RF-G69 (equitativo)
            $item['porcentaje_dividido'] = $totalSucursales > 0 ? (1 / $totalSucursales) * 100 : 0;
            
            return $item;
        })->sortByDesc('ventas')->values();
    }

    /**
     * Obtiene el top 5 de mejores productos de las sedes (RF-G68)
     */
    public function getTopProductsProperty()
    {
        $empresaId = Auth::user()->empresa_id;
        
        // Obtener IDs de las sucursales activas
        $activeBranches = Sucursal::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->pluck('id');

        if ($this->sucursalFilter && $this->sucursalFilter !== 'all') {
            $branchScope = [$this->sucursalFilter];
        } else {
            $branchScope = $activeBranches->toArray();
        }

        $query = DetallePedido::whereIn('sucursal_id', $branchScope)
            ->whereHas('pedido', function ($q) {
                $q->where('estado', '!=', 'cancelado');
                if ($this->startDate) {
                    $q->whereDate('creado_en', '>=', $this->startDate);
                }
                if ($this->endDate) {
                    $q->whereDate('creado_en', '<=', $this->endDate);
                }
            });

        return $query->select(
                'producto_id',
                'nombre_producto',
                DB::raw('SUM(cantidad) as cantidad_vendida'),
                DB::raw('SUM(subtotal) as total_ventas')
            )
            ->groupBy('producto_id', 'nombre_producto')
            ->orderByDesc('cantidad_vendida')
            ->take(5)
            ->get();
    }

    /**
     * Exporta el reporte global a un archivo CSV (RF-G71)
     */
    public function exportCSV()
    {
        $empresaId = Auth::user()->empresa_id;
        $empresaNombre = Auth::user()->empresa->nombre ?? 'Mi Empresa';
        
        $metrics = $this->metrics;
        $sucursalesData = $this->sucursalesData;
        $topProducts = $this->topProducts;

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Reporte_Global_" . now()->format('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->streamDownload(function() use ($empresaNombre, $metrics, $sucursalesData, $topProducts) {
            $file = fopen('php://output', 'w');
            
            // Forzar codificación UTF-8 para Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezado
            fputcsv($file, ['REPORTE OPERATIVO GLOBAL']);
            fputcsv($file, ['Empresa', $empresaNombre]);
            fputcsv($file, ['Fecha de Emisión', now()->format('d/m/Y H:i:s')]);
            fputcsv($file, ['Rango de Fechas', ($this->startDate ?: 'Inicio') . ' al ' . ($this->endDate ?: 'Fin')]);
            fputcsv($file, ['Sede Filtrada', $this->sucursalFilter === 'all' ? 'Todas las Sedes Activas' : (Sucursal::find($this->sucursalFilter)->nombre ?? 'N/A')]);
            fputcsv($file, []);

            // Métricas (RF-G66)
            fputcsv($file, ['METRICAS GENERALES (Sedes Activas)']);
            fputcsv($file, ['Métrica', 'Valor']);
            fputcsv($file, ['Total Ventas', '$ ' . number_format($metrics['ventas_totales'], 0, ',', '.')]);
            fputcsv($file, ['Total Pedidos', $metrics['total_pedidos']]);
            fputcsv($file, ['Ticket Promedio', '$ ' . number_format($metrics['ticket_promedio'], 0, ',', '.')]);
            fputcsv($file, ['Sucursales Activas', $metrics['sucursales_activas']]);
            fputcsv($file, []);

            // Comparativa de Ventas (RF-G67)
            fputcsv($file, ['TABLA COMPARATIVA DE VENTAS (RF-G67)']);
            fputcsv($file, ['Posición', 'Sede', 'Total Ventas', '% Contribución de Ventas']);
            foreach ($sucursalesData as $index => $suc) {
                fputcsv($file, [
                    ($index + 1),
                    $suc['nombre'],
                    '$ ' . number_format($suc['ventas'], 0, ',', '.'),
                    number_format($suc['porcentaje_ventas'], 2, ',', '.') . '%'
                ]);
            }
            fputcsv($file, []);

            // Comparativa General (RF-G69)
            fputcsv($file, ['TABLA COMPARATIVA GENERAL DE SUCURSALES (RF-G69)']);
            fputcsv($file, ['Sede', 'Ventas ($)', 'Cantidad Pedidos', '% Distribución Equitativa']);
            foreach ($sucursalesData as $suc) {
                fputcsv($file, [
                    $suc['nombre'],
                    '$ ' . number_format($suc['ventas'], 0, ',', '.'),
                    $suc['pedidos'],
                    number_format($suc['porcentaje_dividido'], 2, ',', '.') . '%'
                ]);
            }
            fputcsv($file, []);

            // Top 5 Productos (RF-G68)
            fputcsv($file, ['TOP 5 MEJORES PRODUCTOS (RF-G68)']);
            fputcsv($file, ['Posición', 'Producto', 'Cantidad Vendida', 'Total Recaudado']);
            foreach ($topProducts as $index => $prod) {
                fputcsv($file, [
                    ($index + 1),
                    $prod->nombre_producto,
                    $prod->cantidad_vendida,
                    '$ ' . number_format($prod->total_ventas, 0, ',', '.')
                ]);
            }

            fclose($file);
        }, 'Reporte_Global_' . now()->format('Y-m-d') . '.csv', $headers);
    }

    public function render()
    {
        return view('livewire.gerente.global-reports', [
            'sucursalesSelector' => $this->sucursalesSelector,
            'metrics' => $this->metrics,
            'sucursalesData' => $this->sucursalesData,
            'topProducts' => $this->topProducts,
        ])->layout('layouts.app');
    }
}
