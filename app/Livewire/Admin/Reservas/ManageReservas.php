<?php

namespace App\Livewire\Admin\Reservas;

use Livewire\Component;
use App\Models\ReservaMesa;
use App\Models\Mesa;
use App\Enums\EstadoReserva;
use App\Services\ReservaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ManageReservas extends Component
{
    public $tab = 'proximas'; // proximas, historial, pendientes_pago

    public $filtroFechaInicio;
    public $filtroFechaFin;
    public $filtroEstado;
    public $filtroMesa;

    public $selectedReservaId;
    public $showDetailModal = false;
    public $showCancelModal = false;
    public $motivoCancelacion = '';

    protected ReservaService $reservaService;

    public function boot(ReservaService $reservaService): void
    {
        $this->reservaService = $reservaService;
    }

    public function mount()
    {
        if (!auth()->user()->sucursal_id) {
            return redirect()->route('sucursales');
        }
        $this->filtroFechaInicio = Carbon::today()->format('Y-m-d');
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->limpiarFiltros();
        if ($tab === 'proximas') {
            $this->filtroFechaInicio = Carbon::today()->format('Y-m-d');
        }
    }

    public function limpiarFiltros()
    {
        $this->filtroFechaInicio = null;
        $this->filtroFechaFin = null;
        $this->filtroEstado = null;
        $this->filtroMesa = null;
    }

    public function openDetailModal($id)
    {
        $this->selectedReservaId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->selectedReservaId = null;
        $this->showDetailModal = false;
    }

    public function openCancelModal($id)
    {
        $this->selectedReservaId = $id;
        $this->showCancelModal = true;
        $this->motivoCancelacion = '';
    }

    public function closeCancelModal()
    {
        $this->selectedReservaId = null;
        $this->showCancelModal = false;
        $this->motivoCancelacion = '';
    }

    public function cambiarEstado($reservaId, $nuevoEstado)
    {
        $reserva = ReservaMesa::find($reservaId);
        if (!$reserva) return;

        try {
            $estadoEnum = EstadoReserva::from($nuevoEstado);
            if (!$reserva->estado->puedeTransicionarA($estadoEnum)) {
                session()->flash('error', 'Transición de estado no válida.');
                return;
            }

            $reserva->estado = $estadoEnum;
            $reserva->save();

            session()->flash('success', "El estado de la reserva se actualizó a {$estadoEnum->etiqueta()}.");
        } catch (\ValueError $e) {
            session()->flash('error', 'Estado inválido.');
        }
    }

    public function cancelarReserva()
    {
        $this->validate([
            'motivoCancelacion' => 'required|string|min:5|max:255'
        ]);

        $reserva = ReservaMesa::find($this->selectedReservaId);
        if ($reserva && $reserva->estado->puedeTransicionarA(EstadoReserva::CANCELADA)) {
            
            $this->reservaService->cancelarReserva($reserva, 'administrador', $this->motivoCancelacion);

            session()->flash('success', "Reserva cancelada correctamente.");
            $this->closeCancelModal();
            $this->closeDetailModal();
        } else {
            session()->flash('error', "No se puede cancelar esta reserva.");
        }
    }

    public function render()
    {
        $user = auth()->user();
        $sucursal_id = $user->sucursal_id;

        $query = ReservaMesa::with(['mesas', 'pagosDeposito'])
            ->where('sucursal_id', $sucursal_id);

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }
        if ($this->filtroFechaInicio) {
            $query->whereDate('fecha_reserva', '>=', $this->filtroFechaInicio);
        }
        if ($this->filtroFechaFin) {
            $query->whereDate('fecha_reserva', '<=', $this->filtroFechaFin);
        }
        if ($this->filtroMesa) {
            $query->whereHas('mesas', function($q) {
                $q->where('mesas.id', $this->filtroMesa);
            });
        }

        $todasLasReservas = $query->orderBy('fecha_reserva', 'asc')->orderBy('hora_inicio', 'asc')->get();

        $reservasProximas = $todasLasReservas->filter(function($r) {
            return in_array($r->estado->value, [
                EstadoReserva::PENDIENTE->value, 
                EstadoReserva::CONFIRMADA->value, 
                EstadoReserva::CLIENTE_LLEGO->value
            ]);
        });

        $reservasPendientesPago = $todasLasReservas->filter(function($r) {
            return $r->estado->value === EstadoReserva::PENDIENTE_PAGO->value;
        });

        $reservasHistorial = $todasLasReservas->filter(function($r) {
            return in_array($r->estado->value, [
                EstadoReserva::COMPLETADA->value, 
                EstadoReserva::CANCELADA->value, 
                EstadoReserva::NO_SHOW->value
            ]);
        });

        $reservas = $query->orderBy('fecha_reserva', 'asc')->orderBy('hora_inicio', 'asc')->get();
        $mesas = Mesa::where('sucursal_id', $sucursal_id)->get();

        $selectedReserva = $this->selectedReservaId
            ? ReservaMesa::with(['mesas', 'pagosDeposito'])->find($this->selectedReservaId)
            : null;

        return view('livewire.admin.reservas.manage-reservas', [
            'reservasProximas' => $reservasProximas,
            'reservasPendientesPago' => $reservasPendientesPago,
            'reservasHistorial' => $reservasHistorial,
            'mesas' => $mesas,
            'selectedReserva' => $selectedReserva,
            'estados' => EstadoReserva::cases(),
        ])->layout('layouts.admin');
    }
}
