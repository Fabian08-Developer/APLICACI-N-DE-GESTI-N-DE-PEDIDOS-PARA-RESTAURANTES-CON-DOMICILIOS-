<?php

namespace App\Livewire\Admin\Pedidos;

use Livewire\Component;
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
use Illuminate\Support\Facades\Log;

class ManagePedidos extends Component
{
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
    }

    public function limpiarFiltros()
    {
        $this->filtroFechaInicio = null;
        $this->filtroFechaFin = null;
        $this->filtroEstado = null;
        $this->filtroMesa = null;
        $this->filtroMesero = null;
        $this->filtroDomiciliario = null;
        $this->filtroZona = null;
    }

    public function openDetailModal($id)
    {
        $this->selectedPedidoId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->selectedPedidoId = null;
        $this->showDetailModal = false;
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
            // La lógica de validación de bloqueo y actualización de estado
            // del domiciliario vive en el servicio de dominio.
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
     * RF-188: Asignación automática inteligente con 5 criterios:
     * 1. Solo disponibles
     * 2. Sin bloqueo (sin liquidaciones pendientes, sin exceder límite efectivo)
     * 3. Priorizar domiciliarios de la misma zona del pedido
     * 4. Menor carga de trabajo (pedidos_hoy ASC)
     * 5. Desempate: menor cantidad de pedidos activos
     *
     * La selección del candidato permanece aquí (lógica de UI/orquestación).
     * La asignación efectiva y sus invariantes de dominio van al PedidoService.
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

        // 1 & 2. Solo disponibles sin bloqueo
        $candidatos = PerfilDomiciliario::with(['liquidaciones', 'pedidosActivos'])
            ->where('sucursal_id', $sucursal_id)
            ->where('estado', 'disponible')
            ->get()
            ->filter(fn ($d) => !$d->tiene_bloqueo);

        if ($candidatos->isEmpty()) {
            session()->flash('error', 'No hay domiciliarios disponibles para asignar. Puede que todos tengan liquidaciones pendientes o hayan superado el límite de efectivo.');
            return;
        }

        // 3. Priorizar por zona del pedido
        $mismaZona  = $candidatos->where('zona_id', $pedido->zona_id);
        $candidatos = $mismaZona->isNotEmpty() ? $mismaZona : $candidatos;

        // 4 & 5. Menor carga (pedidos_hoy) y desempate por pedidos activos
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
            // El servicio ejecuta la asignación con sus invariantes de dominio
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

        // Capturar estado anterior antes de la actualización para el evento
        $estadoAnterior = $pedido->estado;

        try {
            // El servicio valida el Enum, aplica timestamps y acredita efectivo al domiciliario.
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
                'estado' => EstadoPedido::CANCELADO->value,
                'motivo_cancelacion' => $this->motivoCancelacion
            ]);

            // Save history log
            HistorialEstadoPedido::create([
                'pedido_id'   => $pedido->id,
                'sucursal_id' => $pedido->sucursal_id,
                'estado'      => EstadoPedido::CANCELADO->value,
                'usuario_id'  => auth()->id(),
                'cambiado_en' => now(),
            ]);

            // R-02: Dispatch al final.
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
        $user = auth()->user();
        $sucursal_id = $user->sucursal_id;

        $query = Pedido::with([
            'sesionCliente.mesa',
            'mesero',
            'domiciliario.usuario',
            'zona'
        ])
        ->withCount('detalles')
        ->where('sucursal_id', $sucursal_id);

        // Filter based on selected tab
        if ($this->tab === TipoPedido::LOCAL->value) {
            $query->where('tipo', TipoPedido::LOCAL->value);
            if ($this->filtroMesa) {
                $query->whereHas('sesionCliente', function ($q) {
                    $q->where('mesa_id', $this->filtroMesa);
                });
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

        // General Filters
        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }
        if ($this->filtroFechaInicio) {
            $query->whereDate('creado_en', '>=', $this->filtroFechaInicio);
        }
        if ($this->filtroFechaFin) {
            $query->whereDate('creado_en', '<=', $this->filtroFechaFin);
        }

        $pedidos = $query->latest('creado_en')->get();

        // Dropdowns for filters
        $mesas = Mesa::where('sucursal_id', $sucursal_id)->get();
        $meseros = User::where('sucursal_id', $sucursal_id)
            ->where('rol', 'mesero')
            ->get();
        $domiciliarios = PerfilDomiciliario::with('usuario')
            ->where('sucursal_id', $sucursal_id)
            ->get();
        $zonas = ZonaCobertura::where('sucursal_id', $sucursal_id)->get();

        // Statistics
        $today = Carbon::today();
        $totalPedidosHoy = Pedido::where('sucursal_id', $sucursal_id)
            ->whereDate('creado_en', $today)
            ->count();
        $pendientesHoy = Pedido::where('sucursal_id', $sucursal_id)
            ->whereDate('creado_en', $today)
            ->whereNotIn('estado', [EstadoPedido::ENTREGADO->value, EstadoPedido::CANCELADO->value])
            ->count();
        $completadosHoy = Pedido::where('sucursal_id', $sucursal_id)
            ->whereDate('creado_en', $today)
            ->where('estado', EstadoPedido::ENTREGADO->value)
            ->count();

        // Active delivery count for badge
        $cantDomiciliosActivos = Pedido::where('sucursal_id', $sucursal_id)
            ->where('tipo', TipoPedido::DOMICILIO->value)
            ->whereNotIn('estado', [EstadoPedido::ENTREGADO->value, EstadoPedido::CANCELADO->value])
            ->count();

        // Load selected order details
        $selectedPedido = $this->selectedPedidoId
            ? Pedido::with([
                'sesionCliente.mesa',
                'mesero',
                'detalles.producto',
                'domiciliario.usuario',
                'zona',
                'historial.usuario',
                'pagos'
            ])->find($this->selectedPedidoId)
            : null;

        return view('livewire.admin.pedidos.manage-pedidos', [
            'pedidos' => $pedidos,
            'mesas' => $mesas,
            'meseros' => $meseros,
            'domiciliarios' => $domiciliarios,
            'zonas' => $zonas,
            'totalPedidosHoy' => $totalPedidosHoy,
            'pendientesHoy' => $pendientesHoy,
            'completadosHoy' => $completadosHoy,
            'cantDomiciliosActivos' => $cantDomiciliosActivos,
            'selectedPedido' => $selectedPedido,
        ])->layout('layouts.admin');
    }
}
