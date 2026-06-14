<?php

namespace App\Livewire\Domiciliario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Pedido;
use App\Models\HistorialEstadoPedido;
use App\Enums\EstadoPedido;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

#[Layout('layouts.domiciliario')]
class Dashboard extends Component
{
    public $activeTab = 'pedidos';
    
    // UI state for modals
    public $showConfirmStateModal = false;
    public $showProblemaModal = false;
    public $selectedPedidoId = null;

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleDisponibilidad()
    {
        $perfil = Auth::user()->perfilDomiciliario;
        if ($perfil) {
            $nuevoEstado = $perfil->estado === 'disponible' ? 'inactivo' : 'disponible';
            $perfil->update(['estado' => $nuevoEstado]);
        }
    }

    public function openConfirmModal($pedidoId)
    {
        $this->selectedPedidoId = $pedidoId;
        $this->showConfirmStateModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmStateModal = false;
        $this->selectedPedidoId = null;
    }

    public function confirmarEstado()
    {
        $pedido = Pedido::find($this->selectedPedidoId);
        
        $user = Auth::user();
        if (!$user) {
            $this->closeConfirmModal();
            return;
        }

        if (!$pedido || !$pedido->perfil_domiciliario_id || $pedido->perfil_domiciliario_id !== $user->perfilDomiciliario?->id) {
            $this->closeConfirmModal();
            return;
        }

        $nuevoEstado = null;
        
        // Solo puede iniciar entrega si está LISTO
        if ($pedido->estado === EstadoPedido::LISTO->value) {
            $nuevoEstado = EstadoPedido::EN_CAMINO->value;
        } elseif ($pedido->estado === EstadoPedido::EN_CAMINO->value) {
            // Validar distancia antes de confirmar entrega (50 metros = 0.05 km)
            $puedeEntregar = true;
            if ($user->perfilDomiciliario && $user->perfilDomiciliario->latitud && $pedido->latitud_entrega) {
                try {
                    $url = "http://router.project-osrm.org/route/v1/driving/{$user->perfilDomiciliario->longitud},{$user->perfilDomiciliario->latitud};{$pedido->longitud_entrega},{$pedido->latitud_entrega}?overview=false";
                    $response = \Illuminate\Support\Facades\Http::timeout(3)->get($url);
                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['routes'][0])) {
                            $distancia_km = round($data['routes'][0]['distance'] / 1000, 3);
                            if ($distancia_km > 0.05) {
                                $puedeEntregar = false;
                            }
                        }
                    }
                } catch (\Exception $e) {}
            }
            
            if (!$puedeEntregar) {
                $this->dispatch('error-distancia');
                $this->closeConfirmModal();
                return;
            }

            $nuevoEstado = EstadoPedido::ENTREGADO->value;
            $pedido->entregado_en = now();
        } else {
            // Si el estado no es válido para cambiar (ej. CREADO, EN_PREPARACION), no hacer nada
            $this->closeConfirmModal();
            return;
        }

        if ($nuevoEstado) {
            $pedido->estado = $nuevoEstado;
            $pedido->save();

            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'sucursal_id' => $pedido->sucursal_id,
                'estado' => $nuevoEstado,
                'usuario_id' => Auth::id(),
                'cambiado_en' => now(),
            ]);

            // Si entregamos, actualizar efectivo pendiente y ver si hay más activos
            if ($nuevoEstado === EstadoPedido::ENTREGADO->value) {
                // Sumar efectivo pendiente al domiciliario si pagaron en efectivo
                $metodo = strtolower($pedido->metodo_pago ?? 'efectivo');
                if (empty($metodo) || $metodo === 'efectivo' || $metodo === 'cash') {
                    if ($user->perfilDomiciliario) {
                        $monto_adeudado = max(0, $pedido->total - $pedido->costo_envio);
                        $user->perfilDomiciliario->efectivo_pendiente += $monto_adeudado;
                        $user->perfilDomiciliario->save();
                    }
                }

                $tieneMasPedidos = Pedido::where('perfil_domiciliario_id', Auth::user()->perfilDomiciliario->id)
                    ->whereNotIn('estado', [EstadoPedido::ENTREGADO->value, EstadoPedido::CANCELADO->value])
                    ->exists();
                
                if (!$tieneMasPedidos) {
                    Auth::user()->perfilDomiciliario->update(['estado' => 'disponible']);
                }
            }

            // Fire an event to notify frontend (e.g. for SweetAlert)
            $this->dispatch('estado-actualizado', estado: $nuevoEstado);
        }

        $this->closeConfirmModal();
    }

    public function actualizarUbicacion($latitud, $longitud)
    {
        $user = Auth::user();
        if ($user && $user->perfilDomiciliario) {
            $user->perfilDomiciliario->update([
                'latitud' => $latitud,
                'longitud' => $longitud,
                'ultima_ubicacion_en' => now(),
            ]);
            $this->dispatch('ubicacion-actualizada');
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $perfil = $user->perfilDomiciliario;
        
        $domiciliario = [
            'nombre' => $user->nombre,
            'codigo' => 'DOM' . substr($user->id, 0, 5),
            'estado' => $perfil ? $perfil->estado : 'disponible',
            'calificacion' => $perfil ? $perfil->calificacion : 5.0,
            'vehiculo' => $perfil ? $perfil->tipo_vehiculo : 'N/A',
            'placa' => $perfil ? $perfil->placa : 'N/A',
            'zona' => ($perfil && $perfil->zona) ? $perfil->zona->nombre : 'Sin zona',
            'iniciales' => $perfil ? $perfil->iniciales : 'NA',
        ];

        // Pedidos activos
        $pedidos = collect();
        $historial = collect();
        $estadisticas = [
            'dia' => ['entregas' => 0, 'ganancias' => 0, 'pendientes' => 0],
            'mes' => ['entregas' => 0, 'ganancias' => 0]
        ];

        if ($perfil) {
            $pedidos = Pedido::with(['sesionCliente', 'detalles'])
                ->where('perfil_domiciliario_id', $perfil->id)
                ->whereNotIn('estado', [EstadoPedido::ENTREGADO->value, EstadoPedido::CANCELADO->value])
                ->latest('actualizado_en')
                ->get();

            // Calculate ETA and Distance via OSRM if coordinates exist
            if ($perfil->latitud && $perfil->longitud) {
                foreach ($pedidos as $pedido) {
                    if ($pedido->latitud_entrega && $pedido->longitud_entrega) {
                        try {
                            $url = "http://router.project-osrm.org/route/v1/driving/{$perfil->longitud},{$perfil->latitud};{$pedido->longitud_entrega},{$pedido->latitud_entrega}?overview=false";
                            $response = \Illuminate\Support\Facades\Http::timeout(3)->get($url);
                            if ($response->successful()) {
                                $data = $response->json();
                                if (isset($data['routes'][0])) {
                                    $route = $data['routes'][0];
                                    $pedido->distancia_km = round($route['distance'] / 1000, 1);
                                    $pedido->tiempo_min = ceil($route['duration'] / 60);
                                }
                            }
                        } catch (\Exception $e) {
                            // Silently fail if OSRM is down
                        }
                    }
                }
            }

            $historial = Pedido::with(['sesionCliente'])
                ->where('perfil_domiciliario_id', $perfil->id)
                ->where('estado', EstadoPedido::ENTREGADO->value)
                ->whereDate('entregado_en', Carbon::today())
                ->latest('entregado_en')
                ->get();

            $estadisticas['dia']['entregas'] = $historial->count();
            $estadisticas['dia']['ganancias'] = $historial->sum('costo_envio');
            $estadisticas['dia']['pendientes'] = $pedidos->count();
            $estadisticas['dia']['km_recorrer'] = $pedidos->sum('distancia_km');
            
            // Calculos de Efectivo
            $estadisticas['dia']['por_cobrar'] = $pedidos->where('metodo_pago', 'Efectivo')->sum('total');
            $estadisticas['dia']['efectivo_recibido'] = $historial->where('metodo_pago', 'Efectivo')->sum('total');
            // Cuánto debe entregar al restaurante: Efectivo recibido - Ganancias (costo_envio)
            $estadisticas['dia']['a_liquidar'] = max(0, $estadisticas['dia']['efectivo_recibido'] - $estadisticas['dia']['ganancias']);

            $mesPedidos = Pedido::where('perfil_domiciliario_id', $perfil->id)
                ->where('estado', EstadoPedido::ENTREGADO->value)
                ->whereMonth('entregado_en', Carbon::now()->month)
                ->get();
                
            $estadisticas['mes']['entregas'] = $mesPedidos->count();
            $estadisticas['mes']['ganancias'] = $mesPedidos->sum('costo_envio');
        }

        return view('domiciliario.dashboard', [
            'domiciliario' => $domiciliario,
            'pedidos' => $pedidos,
            'historial' => $historial,
            'estadisticas' => $estadisticas
        ]);
    }
}
