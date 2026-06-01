<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Pedido;
use App\Models\Mesa;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminDashboard extends Component
{
    public function mount()
    {
        if (!Auth::user()->sucursal_id) {
            return redirect()->route('sucursales');
        }
    }

    public function render()
    {
        $user = Auth::user();
        $sucursal_id = $user->sucursal_id;
        $today = Carbon::today();

        // 1. Pedidos Hoy
        $pedidosHoy = Pedido::where('sucursal_id', $sucursal_id)
            ->whereDate('creado_en', $today)
            ->count();

        // 2. Mesas Disponibles y Ocupación
        $mesasTotales = Mesa::where('sucursal_id', $sucursal_id)->count();
        $mesasDisponibles = Mesa::where('sucursal_id', $sucursal_id)->where('estado', 'disponible')->count();
        $mesasOcupadas = $mesasTotales - $mesasDisponibles;
        
        $porcentajeOcupacion = $mesasTotales > 0 ? round(($mesasOcupadas / $mesasTotales) * 100) : 0;

        // 3. Usuarios Activos (Usuarios de la sucursal)
        $usuariosActivos = User::where('sucursal_id', $sucursal_id)->where('activo', true)->count();

        // 4. Últimos Pedidos Registrados
        $ultimosPedidos = Pedido::with('mesero')
            ->where('sucursal_id', $sucursal_id)
            ->latest('creado_en')
            ->take(5)
            ->get();

        return view('livewire.dashboard.admin-dashboard', [
            'pedidosHoy' => $pedidosHoy,
            'mesasTotales' => $mesasTotales,
            'mesasDisponibles' => $mesasDisponibles,
            'mesasOcupadas' => $mesasOcupadas,
            'porcentajeOcupacion' => $porcentajeOcupacion,
            'usuariosActivos' => $usuariosActivos,
            'ultimosPedidos' => $ultimosPedidos,
        ])->layout('layouts.admin');
    }
}
