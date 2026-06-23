<?php

namespace App\Livewire\Admin\Reservas;

use Livewire\Component;
use App\Models\ReservaMesa;
use App\Models\Mesa;
use App\Enums\EstadoReserva;
use App\Services\ReservaService;
use Carbon\Carbon;

class ManageReservas extends Component
{
    public $tab = 'calendario'; // calendario, historial

    // Filtros del historial
    public $filtroFechaInicio;
    public $filtroFechaFin;
    public $filtroEstado;
    public $filtroMesa;
    public $busquedaHistorial = '';

    // Fecha del Gantt / KPI
    public $fechaGantt;

    // Drawer
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
        $this->fechaGantt = Carbon::today()->format('Y-m-d');
    }

    // ─── Tabs ─────────────────────────────────────────────────────────────────

    public function setTab($tab)
    {
        $this->tab = $tab;
        if ($tab === 'historial') {
            $this->filtroFechaInicio = null;
            $this->filtroFechaFin    = null;
            $this->filtroEstado      = null;
            $this->filtroMesa        = null;
        }
    }

    // ─── Fecha Gantt ──────────────────────────────────────────────────────────

    public function setFechaGantt(string $fecha)
    {
        $this->fechaGantt = $fecha;
    }

    // ─── Filtros historial ───────────────────────────────────────────────────

    public function limpiarFiltros()
    {
        $this->filtroFechaInicio  = null;
        $this->filtroFechaFin     = null;
        $this->filtroEstado       = null;
        $this->filtroMesa         = null;
        $this->busquedaHistorial  = '';
    }

    // ─── Drawer de detalle ───────────────────────────────────────────────────

    /**
     * Abre el drawer lateral con los detalles de la reserva.
     * Despacha el evento 'open-detail-drawer' que Alpine escucha en el window
     * para mostrar el overlay sin condiciones de carrera.
     */
    public function openDetailModal($id)
    {
        $this->selectedReservaId = $id;
        $this->showDetailModal   = true;
        $this->dispatch('open-detail-drawer');
    }

    public function closeDetailModal()
    {
        $this->selectedReservaId = null;
        $this->showDetailModal   = false;
        $this->dispatch('close-detail-drawer');
    }

    // ─── Modal de cancelación ────────────────────────────────────────────────

    public function openCancelModal($id)
    {
        $this->selectedReservaId = $id;
        $this->showCancelModal   = true;
        $this->motivoCancelacion = '';
    }

    public function closeCancelModal()
    {
        $this->selectedReservaId = null;
        $this->showCancelModal   = false;
        $this->motivoCancelacion = '';
    }

    // ─── Acciones de estado ──────────────────────────────────────────────────

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
            session()->flash('success', "Estado actualizado a {$estadoEnum->etiqueta()}.");
            // Re-abrir el drawer con los datos frescos
            $this->dispatch('open-detail-drawer');
        } catch (\ValueError $e) {
            session()->flash('error', 'Estado inválido.');
        }
    }

    public function cancelarReserva()
    {
        $this->validate(['motivoCancelacion' => 'required|string|min:5|max:255']);

        $reserva = ReservaMesa::find($this->selectedReservaId);
        if ($reserva && $reserva->estado->puedeTransicionarA(EstadoReserva::CANCELADA)) {
            $this->reservaService->cancelarReserva($reserva, 'administrador', $this->motivoCancelacion);
            session()->flash('success', 'Reserva cancelada correctamente.');
            $this->closeCancelModal();
            $this->closeDetailModal();
        } else {
            session()->flash('error', 'No se puede cancelar esta reserva.');
        }
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        $user        = auth()->user();
        $sucursal_id = $user->sucursal_id;
        $mesas       = Mesa::where('sucursal_id', $sucursal_id)->orderBy('numero')->get();
        $fecha       = $this->fechaGantt ?? Carbon::today()->format('Y-m-d');

        // ── KPIs del día ──────────────────────────────────────────────────────
        $reservasHoy = ReservaMesa::with('mesas')
            ->where('sucursal_id', $sucursal_id)
            ->whereDate('fecha_reserva', $fecha)
            ->get();

        $horaActual = Carbon::now()->format('H:i:s');
        $mesasOcupadasAhora = $reservasHoy->filter(function ($r) use ($horaActual, $fecha) {
            return $r->fecha_reserva->format('Y-m-d') === Carbon::today()->format('Y-m-d')
                && $r->hora_inicio <= $horaActual
                && $r->hora_fin    >= $horaActual
                && $r->estado->estaActiva();
        })->flatMap(fn($r) => $r->mesas->pluck('id'))->unique()->count();

        $kpis = [
            'total_hoy'       => $reservasHoy->count(),
            'confirmadas'     => $reservasHoy->filter(fn($r) => $r->estado->value === EstadoReserva::CONFIRMADA->value)->count(),
            'clientes_aca'    => $reservasHoy->filter(fn($r) => $r->estado->value === EstadoReserva::CLIENTE_LLEGO->value)->count(),
            'pendientes_pago' => $reservasHoy->filter(fn($r) => $r->estado->value === EstadoReserva::PENDIENTE_PAGO->value)->count(),
            'mesas_libres'    => max(0, $mesas->count() - $mesasOcupadasAhora),
        ];

        // ── Gantt Data ────────────────────────────────────────────────────────
        $reservasDelDia = ReservaMesa::with(['mesas', 'pagosDeposito'])
            ->where('sucursal_id', $sucursal_id)
            ->whereDate('fecha_reserva', $fecha)
            ->whereIn('estado', [
                EstadoReserva::PENDIENTE_PAGO->value,
                EstadoReserva::PENDIENTE->value,
                EstadoReserva::CONFIRMADA->value,
                EstadoReserva::CLIENTE_LLEGO->value,
            ])
            ->orderBy('hora_inicio')
            ->get();

        $colorMap = [
            EstadoReserva::CONFIRMADA->value     => '#10b981',
            EstadoReserva::PENDIENTE->value      => '#f59e0b',
            EstadoReserva::PENDIENTE_PAGO->value => '#eab308',
            EstadoReserva::CLIENTE_LLEGO->value  => '#3b82f6',
        ];

        $ganttData = $mesas->map(function ($mesa) use ($reservasDelDia, $colorMap) {
            $reservasDeMesa = $reservasDelDia->filter(fn($r) => $r->mesas->contains('id', $mesa->id))
                ->map(fn($r) => [
                    'id'           => $r->id,
                    'codigo'       => $r->codigo_reserva,
                    'cliente'      => $r->nombre_cliente,
                    'personas'     => $r->numero_personas,
                    'hora_inicio'  => substr($r->hora_inicio, 0, 5),
                    'hora_fin'     => substr($r->hora_fin, 0, 5),
                    'estado'       => $r->estado->etiqueta(),
                    'estado_value' => $r->estado->value,
                    'color'        => $colorMap[$r->estado->value] ?? '#6b7280',
                    'deposito_pagado' => $r->deposito_pagado,
                    'telefono'     => $r->telefono_cliente,
                ])->values()->toArray();

            return [
                'id'        => $mesa->id,
                'numero'    => $mesa->numero,
                'capacidad' => $mesa->capacidad,
                'reservas'  => $reservasDeMesa,
            ];
        })->values()->toArray();

        // ── Eventos FullCalendar ──────────────────────────────────────────────
        $todasReservas = ReservaMesa::with('mesas')
            ->where('sucursal_id', $sucursal_id)
            ->whereIn('estado', [
                EstadoReserva::PENDIENTE_PAGO->value,
                EstadoReserva::PENDIENTE->value,
                EstadoReserva::CONFIRMADA->value,
                EstadoReserva::CLIENTE_LLEGO->value,
            ])
            ->get();

        $eventosCalendario = $todasReservas->map(function ($r) use ($colorMap) {
            $mesas = $r->mesas->count() > 0 ? ' · M:' . $r->mesas->pluck('numero')->join(',') : '';
            return [
                'id'        => $r->id,
                'title'     => $r->nombre_cliente . $mesas,
                'start'     => $r->fecha_reserva->format('Y-m-d') . 'T' . $r->hora_inicio,
                'end'       => $r->fecha_reserva->format('Y-m-d') . 'T' . $r->hora_fin,
                'color'     => $colorMap[$r->estado->value] ?? '#6b7280',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'estado'   => $r->estado->etiqueta(),
                    'personas' => $r->numero_personas,
                    'mesas'    => $r->mesas->pluck('numero')->join(', '),
                    'deposito' => $r->deposito_pagado ? 'Pagado' : 'Pendiente',
                    'codigo'   => $r->codigo_reserva,
                    'telefono' => $r->telefono_cliente,
                ],
            ];
        })->values()->toJson();

        $this->dispatch('update-calendar-events', events: $eventosCalendario);

        // ── Historial ─────────────────────────────────────────────────────────
        $histQuery = ReservaMesa::with(['mesas'])
            ->where('sucursal_id', $sucursal_id)
            ->whereIn('estado', [
                EstadoReserva::COMPLETADA->value,
                EstadoReserva::CANCELADA->value,
                EstadoReserva::NO_SHOW->value,
            ]);

        if ($this->filtroFechaInicio) $histQuery->whereDate('fecha_reserva', '>=', $this->filtroFechaInicio);
        if ($this->filtroFechaFin)    $histQuery->whereDate('fecha_reserva', '<=', $this->filtroFechaFin);
        if ($this->filtroEstado)      $histQuery->where('estado', $this->filtroEstado);
        if ($this->filtroMesa)        $histQuery->whereHas('mesas', fn($q) => $q->where('mesas.id', $this->filtroMesa));
        if ($this->busquedaHistorial) {
            $b = $this->busquedaHistorial;
            $histQuery->where(fn($q) => $q
                ->where('nombre_cliente',  'like', "%{$b}%")
                ->orWhere('codigo_reserva','like', "%{$b}%")
                ->orWhere('telefono_cliente','like', "%{$b}%")
            );
        }

        $reservasHistorial = $histQuery->orderBy('fecha_reserva', 'desc')->orderBy('hora_inicio', 'desc')->get();

        $selectedReserva = $this->selectedReservaId
            ? ReservaMesa::with(['mesas', 'pagosDeposito'])->find($this->selectedReservaId)
            : null;

        return view('livewire.admin.reservas.manage-reservas', [
            'eventosCalendario' => $eventosCalendario,
            'ganttData'         => $ganttData,
            'kpis'              => $kpis,
            'fechaGantt'        => $fecha,
            'mesas'             => $mesas,
            'reservasHistorial' => $reservasHistorial,
            'selectedReserva'   => $selectedReserva,
            'estados'           => EstadoReserva::cases(),
        ])->layout('layouts.admin');
    }
}
