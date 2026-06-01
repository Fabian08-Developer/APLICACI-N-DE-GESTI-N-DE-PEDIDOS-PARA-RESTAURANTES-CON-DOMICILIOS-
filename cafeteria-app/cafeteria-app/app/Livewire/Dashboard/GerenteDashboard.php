<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Pedido;
use App\Models\Sucursal;
use App\Models\SesionCliente;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GerenteDashboard extends Component
{
    public function getStatsProperty()
    {
        $userId = auth()->id();
        $empresaId = auth()->user()->empresa_id;

        return Cache::remember("stats_gerente_{$userId}", now()->addMinutes(5), function () use ($empresaId) {
            $today = Carbon::today();

            $ventasHoy = Pedido::whereHas('sucursal', function ($query) use ($empresaId) {
                    $query->where('empresa_id', $empresaId);
                })
                ->whereNotIn('estado', ['cancelado', 'CANCELADO'])
                ->whereDate('creado_en', $today)
                ->sum('total');

            $pedidosHoy = Pedido::whereHas('sucursal', function ($query) use ($empresaId) {
                    $query->where('empresa_id', $empresaId);
                })
                ->whereNotIn('estado', ['cancelado', 'CANCELADO'])
                ->whereDate('creado_en', $today)
                ->count();

            $clientesHoy = SesionCliente::whereHas('sucursal', function ($query) use ($empresaId) {
                    $query->where('empresa_id', $empresaId);
                })
                ->whereDate('creado_en', $today)
                ->count();

            $sucursales = Sucursal::where('empresa_id', $empresaId)->get();
            
            return [
                'ventas_hoy' => $ventasHoy,
                'pedidos_hoy' => $pedidosHoy,
                'clientes_hoy' => $clientesHoy,
                'sedes_activas' => $sucursales->where('activo', true)->count(),
                'sedes_inactivas' => $sucursales->where('activo', false)->count(),
                'total_sedes' => $sucursales->count(),
            ];
        });
    }

    public function getWeeklySalesProperty()
    {
        $userId = auth()->id();
        $empresaId = auth()->user()->empresa_id;

        return Cache::remember("weekly_sales_gerente_{$userId}", now()->addMinutes(5), function () use ($empresaId) {
            $daysData = [];
            $maxSales = 0;

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                
                $sales = Pedido::whereHas('sucursal', function ($query) use ($empresaId) {
                        $query->where('empresa_id', $empresaId);
                    })
                    ->whereNotIn('estado', ['cancelado', 'CANCELADO'])
                    ->whereDate('creado_en', $date)
                    ->sum('total');

                $dayOfWeek = $date->dayOfWeek;
                $translations = [
                    Carbon::MONDAY => 'Lun',
                    Carbon::TUESDAY => 'Mar',
                    Carbon::WEDNESDAY => 'Mie',
                    Carbon::THURSDAY => 'Jue',
                    Carbon::FRIDAY => 'Vie',
                    Carbon::SATURDAY => 'Sab',
                    Carbon::SUNDAY => 'Dom',
                ];
                $dayLabel = $translations[$dayOfWeek] ?? '';

                if ($sales > $maxSales) {
                    $maxSales = $sales;
                }

                $daysData[] = [
                    'label' => $dayLabel,
                    'sales' => $sales,
                    'date_str' => $date->format('d/m'),
                ];
            }

            foreach ($daysData as &$day) {
                $day['height'] = $maxSales > 0 ? (int)(($day['sales'] / $maxSales) * 100) : 0;
                if ($day['sales'] > 0 && $day['height'] < 8) {
                    $day['height'] = 8;
                }
            }

            return $daysData;
        });
    }

    public function getSucursalesProperty()
    {
        $empresaId = auth()->user()->empresa_id;

        return Sucursal::where('empresa_id', $empresaId)
            ->withCount(['pedidos as pedidos_pendientes_count' => function ($query) {
                $query->whereNotIn('estado', ['entregado', 'cancelado']);
            }])
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.gerente-dashboard', [
            'stats' => $this->stats,
            'sucursales' => $this->sucursales,
            'weeklySales' => $this->weeklySales,
        ])->layout('layouts.app');
    }
}
