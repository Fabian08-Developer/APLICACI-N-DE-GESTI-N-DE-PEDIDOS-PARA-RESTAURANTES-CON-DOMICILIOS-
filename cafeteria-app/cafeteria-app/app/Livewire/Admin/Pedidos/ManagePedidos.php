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
use App\Events\PedidoCambioEstado;
use App\Events\PedidoAsignadoDomiciliario;
use App\Events\PedidoCancelado;
use App\Jobs\DispararNotificacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ManagePedidos extends Component
{
    // Navigation tab
    public $tab = 'local'; // 'local', 'domicilio', 'domicilios_activos'

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

    protected $queryString = [
        'tab' => ['except' => 'local'],
    ];

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
        if (!$pedido || $pedido->tipo !== 'domicilio') {
            return;
        }

        // RF-140: Bloquear si tiene liquidaciones pendientes o superó límite de efectivo
        $domiciliario = PerfilDomiciliario::find($domiciliarioId);
        if (!$domiciliario) {
            session()->flash('error', 'Domiciliario no encontrado.');
            return;
        }

        if ($domiciliario->tiene_bloqueo) {
            session()->flash('error', 'No se puede asignar: el domiciliario tiene efectivo pendiente por liquidar o superó el límite de efectivo permitido.');
            return;
        }

        $pedido->update([
            'perfil_domiciliario_id' => $domiciliarioId
        ]);

        HistorialEstadoPedido::create([
            'pedido_id'   => $pedido->id,
            'sucursal_id' => $pedido->sucursal_id,
            'estado'      => $pedido->estado,
            'usuario_id'  => auth()->id(),
            'cambiado_en' => now(),
        ]);

        if ($domiciliario->estado === 'disponible') {
            $domiciliario->update(['estado' => 'en_ruta']);
        }

        // R-02: Solo se agrega dispatch al final, sin tocar la lógica de negocio.
        PedidoAsignadoDomiciliario::dispatch(
            sucursal_id:           $pedido->sucursal_id,
            pedido_id:             $pedido->id,
            short_id:              $pedido->short_id,
            domiciliario_nombre:   $domiciliario->nombre,
            domiciliario_user_id:  $domiciliario->usuario_id,
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
    }

    /**
     * RF-188: Asignación automática inteligente con 5 criterios:
     * 1. Solo disponibles
     * 2. Sin bloqueo (sin liquidaciones pendientes, sin exceder límite efectivo)
     * 3. Priorizar domiciliarios de la misma zona del pedido
     * 4. Menor carga de trabajo (pedidos_hoy ASC)
     * 5. Desempate: menor cantidad de pedidos activos
     */
    public function autoAsignar($pedidoId)
    {
        $pedido = Pedido::find($pedidoId);
        if (!$pedido || $pedido->tipo !== 'domicilio') {
            session()->flash('error', 'Pedido no válido para auto-asignación.');
            return;
        }

        if ($pedido->perfil_domiciliario_id) {
            session()->flash('error', 'Este pedido ya tiene un domiciliario asignado.');
            return;
        }

        $sucursal_id = auth()->user()->sucursal_id;

        // 1. Solo disponibles
        $candidatos = PerfilDomiciliario::with(['liquidaciones', 'pedidosActivos'])
            ->where('sucursal_id', $sucursal_id)
            ->where('estado', 'disponible')
            ->get();

        // 2. Filtrar los que tienen bloqueo (RF-140)
        $candidatos = $candidatos->filter(fn($d) => !$d->tiene_bloqueo);

        if ($candidatos->isEmpty()) {
            session()->flash('error', 'No hay domiciliarios disponibles para asignar. Puede que todos tengan liquidaciones pendientes o hayan superado el límite de efectivo.');
            return;
        }

        // 3. Priorizar por zona del pedido
        $mismaZona   = $candidatos->where('zona_id', $pedido->zona_id);
        $candidatos  = $mismaZona->isNotEmpty() ? $mismaZona : $candidatos;

        // 4. Menor carga de trabajo (pedidos_hoy ASC) y 5. menor pedidos activos
        $elegido = $candidatos
            ->sortBy([
                ['pedidos_hoy', 'asc'],
                [fn($d) => $d->pedidosActivos->count(), 'asc'],
            ])
            ->first();

        if (!$elegido) {
            session()->flash('error', 'No se pudo determinar un domiciliario adecuado.');
            return;
        }

        $pedido->update(['perfil_domiciliario_id' => $elegido->id]);

        HistorialEstadoPedido::create([
            'pedido_id'   => $pedido->id,
            'sucursal_id' => $pedido->sucursal_id,
            'estado'      => $pedido->estado,
            'usuario_id'  => auth()->id(),
            'cambiado_en' => now(),
        ]);

        $elegido->update(['estado' => 'en_ruta']);

        // R-02: Dispatch al final, misma lógica que asignarDomiciliario()
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
    }

    public function cambiarEstado($pedidoId, $nuevoEstado)
    {
        $pedido = Pedido::find($pedidoId);
        if (!$pedido) {
            return;
        }

        // Validate state exists
        if (!in_array($nuevoEstado, array_column(EstadoPedido::cases(), 'value'))) {
            session()->flash('error', "El estado no existe.");
            return;
        }

        $updateData = ['estado' => $nuevoEstado];
        if ($nuevoEstado === EstadoPedido::LISTO->value) {
            $updateData['listo_en'] = now();
        } elseif ($nuevoEstado === EstadoPedido::ENTREGADO->value) {
            $updateData['entregado_en'] = now();
        }

        $pedido->update($updateData);

        // Actualizar efectivo pendiente del domiciliario si la orden es entregada y pagada en efectivo
        if ($nuevoEstado === EstadoPedido::ENTREGADO->value && $pedido->tipo === 'domicilio' && $pedido->perfil_domiciliario_id) {
            $domiciliario = PerfilDomiciliario::find($pedido->perfil_domiciliario_id);
            if ($domiciliario) {
                $metodo = strtolower($pedido->metodo_pago ?? 'efectivo');
                if (empty($metodo) || $metodo === 'efectivo' || $metodo === 'cash') {
                    $monto_adeudado = max(0, $pedido->total - $pedido->costo_envio);
                    $domiciliario->efectivo_pendiente += $monto_adeudado;
                    $domiciliario->save();
                }
            }
        }

        // Save history log
        HistorialEstadoPedido::create([
            'pedido_id'   => $pedido->id,
            'sucursal_id' => $pedido->sucursal_id,
            'estado'      => $nuevoEstado,
            'usuario_id'  => auth()->id(),
            'cambiado_en' => now(),
        ]);

        // R-02: Dispatch al final, preservando toda la lógica existente.
        PedidoCambioEstado::dispatch(
            sucursal_id:    $pedido->sucursal_id,
            pedido_id:      $pedido->id,
            short_id:       $pedido->short_id,
            estado_anterior: $pedido->getOriginal('estado') ?? $pedido->estado,
            estado_nuevo:   $nuevoEstado,
            tipo_pedido:    $pedido->tipo,
        );

        DispararNotificacion::paraSucursal(
            sucursal_id: $pedido->sucursal_id,
            tipo:        'estado_cambiado',
            titulo:      "Pedido #{$pedido->short_id}: {$nuevoEstado}",
            mensaje:     "El estado del pedido cambió a {$nuevoEstado}",
            datos:       ['pedido_id' => $pedido->id, 'estado' => $nuevoEstado],
        )->dispatch();

        session()->flash('success', "El estado del pedido #{$pedido->id} se actualizó a {$nuevoEstado}.");
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
        if ($this->tab === 'local') {
            $query->where('tipo', 'local');
            if ($this->filtroMesa) {
                $query->whereHas('sesionCliente', function($q) {
                    $q->where('mesa_id', $this->filtroMesa);
                });
            }
            if ($this->filtroMesero) {
                $query->where('mesero_id', $this->filtroMesero);
            }
        } elseif ($this->tab === 'domicilio') {
            $query->where('tipo', 'domicilio');
            if ($this->filtroDomiciliario) {
                $query->where('perfil_domiciliario_id', $this->filtroDomiciliario);
            }
            if ($this->filtroZona) {
                $query->where('zona_id', $this->filtroZona);
            }
        } elseif ($this->tab === 'domicilios_activos') {
            $query->where('tipo', 'domicilio')
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
            ->where('tipo', 'domicilio')
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
