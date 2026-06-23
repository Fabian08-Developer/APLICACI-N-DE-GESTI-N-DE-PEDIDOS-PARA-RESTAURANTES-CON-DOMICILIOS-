<?php

namespace App\Livewire\Admin\Pedidos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pedido;
use App\Models\Mesa;
use App\Models\User;
use App\Models\PerfilDomiciliario;
use App\Models\ZonaCobertura;
use App\Models\HistorialEstadoPedido;
use App\Enums\EstadoPedido;
use App\Enums\TipoPedido;
use App\Services\Cliente\PedidoService;
use App\Events\PedidoCambioEstado;
use App\Events\PedidoAsignadoDomiciliario;
use App\Events\PedidoCancelado;
use App\Jobs\DispararNotificacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManagePedidos extends Component
{
    use WithPagination;

    // Navigation tab
    public $tab = 'local'; // Valores válidos: TipoPedido::LOCAL->value, TipoPedido::DOMICILIO->value, 'domicilios_activos'

    // Reactive filter inputs
    public $filtroFechaInicio;
    public $filtroFechaFin;
    public $filtroEstado;
    public $filtroMesa;
    public $filtroMesero;
    public $filtroDomiciliario;
    public $filtroZona;

    // Selection & Modals
    public $selectedPedidoId;
    public $showDetailModal = false;
    public $showAsignarModal = false;
    public $motivoCancelacion = '';

    /** Servicio de dominio inyectado — no serializado por Livewire (no es public). */
    protected PedidoService $pedidoService;

    protected $queryString = [
        'tab' => ['except' => 'local'],
    ];

    /**
     * Livewire llama boot() en cada request (hydration + dehydration),
     * lo que lo hace el lugar correcto para inyección de dependencias.
     */
    public function boot(PedidoService $pedidoService): void
    {
        $this->pedidoService = $pedidoService;
    }

    public function mount()
    {
        if (!auth()->user()->sucursal_id) {
            return redirect()->route('sucursales');
        }
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->limpiarFiltros();
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->filtroFechaInicio = null;
        $this->filtroFechaFin    = null;
        $this->filtroEstado      = null;
        $this->filtroMesa        = null;
        $this->filtroMesero      = null;
        $this->filtroDomiciliario = null;
        $this->filtroZona        = null;
        $this->resetPage();
    }

    // Reset page on filter change
    public function updatedFiltroEstado()      { $this->resetPage(); }
    public function updatedFiltroMesa()        { $this->resetPage(); }
    public function updatedFiltroMesero()      { $this->resetPage(); }
    public function updatedFiltroDomiciliario(){ $this->resetPage(); }
    public function updatedFiltroZona()        { $this->resetPage(); }
    public function updatedFiltroFechaInicio() { $this->resetPage(); }
    public function updatedFiltroFechaFin()    { $this->resetPage(); }

    public function openDetailModal($id)
    {
        $this->selectedPedidoId = $id;
        $this->showDetailModal  = true;
    }

    public function closeDetailModal()
    {
        $this->selectedPedidoId = null;
        $this->showDetailModal  = false;
        $this->motivoCancelacion = '';
    }

    public function openAsignarModal($id)
    {
        $this->selectedPedidoId = $id;
        $this->showAsignarModal = true;
    }

    public function closeAsignarModal()
    {
        $this->selectedPedidoId = null;
        $this->showAsignarModal = false;
    }

    public function asignarDomiciliario($domiciliarioId)
    {
        $pedido = Pedido::find($this->selectedPedidoId);
        if (!$pedido || $pedido->tipo !== TipoPedido::DOMICILIO->value) {
            return;
        }

        try {
            $domiciliario = $this->pedidoService->asignarDomiciliario($pedido, $domiciliarioId);

            PedidoAsignadoDomiciliario::dispatch(
                sucursal_id:          $pedido->sucursal_id,
                pedido_id:            $pedido->id,
                short_id:             $pedido->short_id,
                domiciliario_nombre:  $domiciliario->nombre,
                domiciliario_user_id: $domiciliario->usuario_id,
            );

            DispararNotificacion::paraSucursal(
                sucursal_id: $pedido->sucursal_id,
                tipo:        'pedido_asignado',
                titulo:      "Pedido #{$pedido->short_id} asignado",
                mensaje:     "Asignado a {$domiciliario->nombre}",
                datos:       ['pedido_id' => $pedido->id],
            )->dispatch();

            session()->flash('success', 'Domiciliario asignado correctamente.');
            $this->closeAsignarModal();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Domiciliario no encontrado.');
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * RF-188: Asignación automática inteligente con 5 criterios.
     * OPTIMIZADO: el filtro de bloqueo ahora se resuelve en SQL, no en PHP.
     */
    public function autoAsignar($pedidoId)
    {
        $pedido = Pedido::find($pedidoId);
        if (!$pedido || $pedido->tipo !== TipoPedido::DOMICILIO->value) {
            session()->flash('error', 'Pedido no válido para auto-asignación.');
            return;
        }

        if ($pedido->perfil_domiciliario_id) {
            session()->flash('error', 'Este pedido ya tiene un domiciliario asignado.');
            return;
        }

        $sucursal_id = auth()->user()->sucursal_id;

        // OPTIMIZADO: filtrar en SQL en lugar de cargar todos y filtrar en PHP
        // Excluye domiciliarios con efectivo_pendiente >= limite_efectivo O con liquidaciones pendientes
        $candidatos = PerfilDomiciliario::with(['pedidosActivos'])
            ->where('sucursal_id', $sucursal_id)
            ->where('estado', 'disponible')
            ->where(function ($q) {
                // Solo los que NO tienen bloqueo por efectivo
                $q->whereRaw('efectivo_pendiente < limite_efectivo');
            })
            ->whereDoesntHave('liquidaciones', function ($q) {
                $q->where('estado', 'pendiente');
            })
            ->get();

        if ($candidatos->isEmpty()) {
            session()->flash('error', 'No hay domiciliarios disponibles para asignar. Puede que todos tengan liquidaciones pendientes o hayan superado el límite de efectivo.');
            return;
        }

        // Priorizar por zona del pedido
        $mismaZona  = $candidatos->where('zona_id', $pedido->zona_id);
        $candidatos = $mismaZona->isNotEmpty() ? $mismaZona : $candidatos;

        // Menor carga (pedidos_hoy) y desempate por pedidos activos
        $elegido = $candidatos
            ->sortBy([
                ['pedidos_hoy', 'asc'],
                [fn ($d) => $d->pedidosActivos->count(), 'asc'],
            ])
            ->first();

        if (!$elegido) {
            session()->flash('error', 'No se pudo determinar un domiciliario adecuado.');
            return;
        }

        try {
            $this->pedidoService->asignarDomiciliario($pedido, $elegido->id);

            PedidoAsignadoDomiciliario::dispatch(
                sucursal_id:          $pedido->sucursal_id,
                pedido_id:            $pedido->id,
                short_id:             $pedido->short_id,
                domiciliario_nombre:  $elegido->nombre,
                domiciliario_user_id: $elegido->usuario_id,
            );

            DispararNotificacion::paraSucursal(
                sucursal_id: $pedido->sucursal_id,
                tipo:        'pedido_asignado',
                titulo:      "Pedido #{$pedido->short_id} auto-asignado",
                mensaje:     "Auto-asignado a {$elegido->nombre} (Zona: {$elegido->zona?->nombre})",
                datos:       ['pedido_id' => $pedido->id],
            )->dispatch();

            session()->flash('success', "Auto-asignado correctamente a {$elegido->nombre} (Zona: {$elegido->zona?->nombre}, Pedidos hoy: {$elegido->pedidos_hoy}).");
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cambiarEstado($pedidoId, $nuevoEstado)
    {
        $pedido = Pedido::find($pedidoId);
        if (!$pedido) {
            return;
        }

        $estadoAnterior = $pedido->estado;

        try {
            $this->pedidoService->cambiarEstado($pedido, $nuevoEstado);

            PedidoCambioEstado::dispatch(
                sucursal_id:     $pedido->sucursal_id,
                pedido_id:       $pedido->id,
                short_id:        $pedido->short_id,
                estado_anterior: $estadoAnterior,
                estado_nuevo:    $nuevoEstado,
                tipo_pedido:     $pedido->tipo,
            );

            DispararNotificacion::paraSucursal(
                sucursal_id: $pedido->sucursal_id,
                tipo:        'estado_cambiado',
                titulo:      "Pedido #{$pedido->short_id}: {$nuevoEstado}",
                mensaje:     "El estado del pedido cambió a {$nuevoEstado}",
                datos:       ['pedido_id' => $pedido->id, 'estado' => $nuevoEstado],
            )->dispatch();

            session()->flash('success', "El estado del pedido #{$pedido->id} se actualizó a {$nuevoEstado}.");
        } catch (\ValueError $e) {
            session()->flash('error', 'El estado proporcionado no es válido.');
        }
    }

    public function cancelarPedido($pedidoId)
    {
        $this->validate([
            'motivoCancelacion' => 'required|string|min:3|max:255'
        ]);

        $pedido = Pedido::find($pedidoId);
        if ($pedido) {
            $pedido->update([
                'estado'             => EstadoPedido::CANCELADO->value,
                'motivo_cancelacion' => $this->motivoCancelacion
            ]);

            HistorialEstadoPedido::create([
                'pedido_id'   => $pedido->id,
                'sucursal_id' => $pedido->sucursal_id,
                'estado'      => EstadoPedido::CANCELADO->value,
                'usuario_id'  => auth()->id(),
                'cambiado_en' => now(),
            ]);

            PedidoCancelado::dispatch(
                sucursal_id: $pedido->sucursal_id,
                pedido_id:   $pedido->id,
                short_id:    $pedido->short_id,
                tipo:        $pedido->tipo,
                motivo:      $this->motivoCancelacion,
            );

            DispararNotificacion::paraSucursal(
                sucursal_id: $pedido->sucursal_id,
                tipo:        'pedido_cancelado',
                titulo:      "Pedido #{$pedido->short_id} cancelado",
                mensaje:     $this->motivoCancelacion,
                datos:       ['pedido_id' => $pedido->id],
            )->dispatch();

            session()->flash('success', "Pedido #{$pedido->id} cancelado correctamente.");
            $this->closeDetailModal();
        }
    }

    public function render()
    {
        $user        = auth()->user();
        $sucursal_id = $user->sucursal_id;

        // ── PEDIDOS (paginados) ───────────────────────────────────────────
        $query = Pedido::with([
                'sesionCliente.mesa',
                'mesero:id,nombre',
                'domiciliario.usuario:id,nombre',
                'zona:id,nombre',
            ])
            ->withCount('detalles')
            ->where('sucursal_id', $sucursal_id);

        // Filter based on selected tab
        if ($this->tab === TipoPedido::LOCAL->value) {
            $query->where('tipo', TipoPedido::LOCAL->value);
            if ($this->filtroMesa) {
                $query->whereHas('sesionCliente', fn($q) =>
                    $q->where('mesa_id', $this->filtroMesa)
                );
            }
            if ($this->filtroMesero) {
                $query->where('mesero_id', $this->filtroMesero);
            }
        } elseif ($this->tab === TipoPedido::DOMICILIO->value) {
            $query->where('tipo', TipoPedido::DOMICILIO->value);
            if ($this->filtroDomiciliario) {
                $query->where('perfil_domiciliario_id', $this->filtroDomiciliario);
            }
            if ($this->filtroZona) {
                $query->where('zona_id', $this->filtroZona);
            }
        } elseif ($this->tab === 'domicilios_activos') {
            $query->where('tipo', TipoPedido::DOMICILIO->value)
                  ->whereNotIn('estado', [EstadoPedido::ENTREGADO->value, EstadoPedido::CANCELADO->value]);
            if ($this->filtroDomiciliario) {
                $query->where('perfil_domiciliario_id', $this->filtroDomiciliario);
            }
            if ($this->filtroZona) {
                $query->where('zona_id', $this->filtroZona);
            }
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }
        if ($this->filtroFechaInicio) {
            $query->whereDate('creado_en', '>=', $this->filtroFechaInicio);
        }
        if ($this->filtroFechaFin) {
            $query->whereDate('creado_en', '<=', $this->filtroFechaFin);
        }

        $pedidos = $query->latest('creado_en')->paginate(20);

        // ── DROPDOWNS (cacheados 60 segundos — no cambian cada request) ──
        $cacheKey = "dropdowns_pedidos_{$sucursal_id}";
        $dropdowns = Cache::remember($cacheKey, 60, function () use ($sucursal_id) {
            return [
                'mesas'         => Mesa::where('sucursal_id', $sucursal_id)
                                       ->select('id', 'numero')
                                       ->get(),
                'meseros'       => User::where('sucursal_id', $sucursal_id)
                                       ->where('rol', 'mesero')
                                       ->select('id', 'nombre')
                                       ->get(),
                'domiciliarios' => PerfilDomiciliario::with('usuario:id,nombre')
                                       ->where('sucursal_id', $sucursal_id)
                                       ->get(),
                'zonas'         => ZonaCobertura::where('sucursal_id', $sucursal_id)
                                       ->select('id', 'nombre')
                                       ->get(),
            ];
        });

        // ── ESTADÍSTICAS HOY — 1 sola query en lugar de 3 ───────────────
        $statsHoy = Pedido::where('sucursal_id', $sucursal_id)
            ->whereDate('creado_en', Carbon::today())
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN estado NOT IN (?, ?) THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END) as completados
            ", [
                EstadoPedido::ENTREGADO->value,
                EstadoPedido::CANCELADO->value,
                EstadoPedido::ENTREGADO->value,
            ])
            ->first();

        // Active delivery count for badge — query separada sólo cuando es necesario
        $cantDomiciliosActivos = Pedido::where('sucursal_id', $sucursal_id)
            ->where('tipo', TipoPedido::DOMICILIO->value)
            ->whereNotIn('estado', [EstadoPedido::ENTREGADO->value, EstadoPedido::CANCELADO->value])
            ->count();

        // ── PEDIDO SELECCIONADO (solo cuando el modal está abierto) ──────
        $selectedPedido = $this->selectedPedidoId
            ? Pedido::with([
                'sesionCliente.mesa',
                'mesero:id,nombre',
                'detalles.producto:id,nombre',
                'domiciliario.usuario:id,nombre',
                'zona:id,nombre',
                'historial.usuario:id,nombre',
                'pagos',
            ])->find($this->selectedPedidoId)
            : null;

        return view('livewire.admin.pedidos.manage-pedidos', [
            'pedidos'               => $pedidos,
            'mesas'                 => $dropdowns['mesas'],
            'meseros'               => $dropdowns['meseros'],
            'domiciliarios'         => $dropdowns['domiciliarios'],
            'zonas'                 => $dropdowns['zonas'],
            'totalPedidosHoy'       => $statsHoy->total ?? 0,
            'pendientesHoy'         => $statsHoy->pendientes ?? 0,
            'completadosHoy'        => $statsHoy->completados ?? 0,
            'cantDomiciliosActivos' => $cantDomiciliosActivos,
            'selectedPedido'        => $selectedPedido,
        ])->layout('layouts.admin');
    }
}
