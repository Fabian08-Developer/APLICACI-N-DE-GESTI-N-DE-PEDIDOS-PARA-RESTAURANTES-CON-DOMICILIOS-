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

    // Modal Posponer — estado multi-paso
    public $showPostponeModal    = false;
    public $postponeFecha        = '';
    public $postponeHoraInicio   = '';
    // 'seleccion' | 'conflicto' | 'manual'
    public $postponeStep         = 'seleccion';
    public $postponeConflictoMsg = '';
    public $postponeNuevaHoraFin = '';
    public $postponeMesasDisponibles  = [];   // [{id, numero, capacidad}]
    public $postponeMesasSeleccionadas = [];  // IDs seleccionados manualmente

    // Bulk (selección masiva en historial)
    public $reservasSeleccionadas    = [];
    public $showBulkPostponeModal    = false;
    public $bulkPostponeFecha        = '';
    public $bulkPostponeHoraInicio   = '';
    // 'seleccion' | 'resultados'
    public $bulkPostponeStep         = 'seleccion';
    public $bulkResultados           = [];

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
        $this->dispatch('open-cancel-modal');
    }

    public function closeCancelModal()
    {
        $this->selectedReservaId = null;
        $this->showCancelModal   = false;
        $this->motivoCancelacion = '';
        $this->dispatch('close-cancel-modal');
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
            $this->reservaService->cancelarReserva($reserva, $this->motivoCancelacion, 'administrador');
            session()->flash('success', 'Reserva cancelada correctamente.');
            $this->closeCancelModal();
            $this->closeDetailModal();
        } else {
            session()->flash('error', 'No se puede cancelar esta reserva.');
        }
    }

    // ─── Modal de Posponer (individual, multi-paso) ──────────────────────────

    public function openPostponeModal($id): void
    {
        $this->selectedReservaId         = $id;
        $this->showPostponeModal         = true;
        $this->postponeFecha             = '';
        $this->postponeHoraInicio        = '';
        $this->postponeStep              = 'seleccion';
        $this->postponeConflictoMsg      = '';
        $this->postponeNuevaHoraFin      = '';
        $this->postponeMesasDisponibles  = [];
        $this->postponeMesasSeleccionadas = [];
        $this->dispatch('open-postpone-modal');
    }

    public function closePostponeModal(): void
    {
        $this->showPostponeModal         = false;
        $this->postponeFecha             = '';
        $this->postponeHoraInicio        = '';
        $this->postponeStep              = 'seleccion';
        $this->postponeConflictoMsg      = '';
        $this->postponeNuevaHoraFin      = '';
        $this->postponeMesasDisponibles  = [];
        $this->postponeMesasSeleccionadas = [];
        $this->dispatch('close-postpone-modal');
    }

    /**
     * Paso 1: Verifica disponibilidad sin guardar.
     *  - Sin conflicto → guarda directamente.
     *  - Con conflicto → cambia a paso 'conflicto' mostrando opciones.
     */
    public function verificarPostponer(): void
    {
        $this->validate([
            'postponeFecha'      => 'required|date|after_or_equal:today',
            'postponeHoraInicio' => 'required|date_format:H:i',
        ], [
            'postponeFecha.required'       => 'Debes elegir una nueva fecha.',
            'postponeFecha.after_or_equal' => 'La fecha no puede ser en el pasado.',
            'postponeHoraInicio.required'  => 'Debes elegir una nueva hora.',
            'postponeHoraInicio.date_format' => 'El formato de hora debe ser HH:MM.',
        ]);

        $reserva = ReservaMesa::with(['mesas'])->find($this->selectedReservaId);
        if (!$reserva || !$reserva->sucursal) {
            session()->flash('error', 'No se pudo cargar la reserva.');
            return;
        }

        try {
            $info = $this->reservaService->verificarConflictoPostponer(
                $reserva,
                $this->postponeFecha,
                $this->postponeHoraInicio,
                $reserva->sucursal
            );

            if (!$info['conflicto']) {
                // Sin conflicto → guardar directamente
                $this->reservaService->posponerReservaConMesas(
                    $reserva,
                    $this->postponeFecha,
                    $this->postponeHoraInicio,
                    $info['nuevaHoraFin'],
                    $info['mesasActualesIds'],
                    $reserva->sucursal
                );
                session()->flash('success', 'Reserva reprogramada. El cliente fue notificado por correo.');
                $this->closePostponeModal();
                $this->closeDetailModal();
            } else {
                // Hay conflicto → mostrar pantalla de decisión
                $this->postponeStep     = 'conflicto';
                $this->postponeNuevaHoraFin = $info['nuevaHoraFin'];
                $mesasConId = Mesa::whereIn('id', $info['mesasConflicto'])->pluck('numero', 'id')->toArray();
                $nums = implode(', ', array_map(fn($num) => "Mesa $num", array_values($mesasConId)));
                $this->postponeConflictoMsg = "Las siguientes mesas ya están ocupadas en ese horario: {$nums}.";
                $this->postponeMesasDisponibles = $info['mesasAlternativas']->map(fn($m) => [
                    'id'       => $m->id,
                    'numero'   => $m->numero,
                    'capacidad'=> $m->capacidad,
                ])->values()->toArray();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('postponeFecha', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            session()->flash('error', 'Error al verificar disponibilidad.');
        }
    }

    /** Paso 2a: Reasignar automáticamente al confirmar conflicto con auto. */
    public function posponerConAutoAsignacion(): void
    {
        $reserva = ReservaMesa::with(['mesas'])->find($this->selectedReservaId);
        if (!$reserva || !$reserva->sucursal) {
            session()->flash('error', 'Reserva no encontrada.');
            return;
        }

        if (empty($this->postponeMesasDisponibles)) {
            session()->flash('error', 'No hay mesas disponibles para este horario. Elige otra fecha u hora.');
            $this->postponeStep = 'seleccion';
            return;
        }

        try {
            $mesasIds = collect($this->postponeMesasDisponibles)->pluck('id')->take(
                $reserva->mesas->count() ?: 1
            )->toArray();

            $this->reservaService->posponerReservaConMesas(
                $reserva,
                $this->postponeFecha,
                $this->postponeHoraInicio,
                $this->postponeNuevaHoraFin,
                $mesasIds,
                $reserva->sucursal
            );
            session()->flash('success', 'Reserva reprogramada con mesas reasignadas. Cliente notificado.');
            $this->closePostponeModal();
            $this->closeDetailModal();
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            session()->flash('error', 'Error al reprogramar. Intenta de nuevo.');
        }
    }

    /** Paso 2b: Avanza al paso de selección manual de mesas. */
    public function irASeleccionManual(): void
    {
        $this->postponeStep              = 'manual';
        $this->postponeMesasSeleccionadas = [];
    }

    /** Paso 3: Confirmar con mesas elegidas manualmente. */
    public function posponerConMesasManual(): void
    {
        if (empty($this->postponeMesasSeleccionadas)) {
            $this->addError('postponeMesasSeleccionadas', 'Debes seleccionar al menos una mesa.');
            return;
        }

        $reserva = ReservaMesa::with(['mesas'])->find($this->selectedReservaId);
        if (!$reserva || !$reserva->sucursal) {
            session()->flash('error', 'Reserva no encontrada.');
            return;
        }

        try {
            $this->reservaService->posponerReservaConMesas(
                $reserva,
                $this->postponeFecha,
                $this->postponeHoraInicio,
                $this->postponeNuevaHoraFin,
                array_map('intval', $this->postponeMesasSeleccionadas),
                $reserva->sucursal
            );
            session()->flash('success', 'Reserva reprogramada con las mesas seleccionadas. Cliente notificado.');
            $this->closePostponeModal();
            $this->closeDetailModal();
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar. Intenta de nuevo.');
        }
    }

    // ─── Selección masiva (historial) ────────────────────────────────────────

    public function toggleReservaSeleccionada($id): void
    {
        if (in_array($id, $this->reservasSeleccionadas)) {
            $this->reservasSeleccionadas = array_values(array_diff($this->reservasSeleccionadas, [$id]));
        } else {
            $this->reservasSeleccionadas[] = $id;
        }
    }

    public function limpiarSeleccion(): void
    {
        $this->reservasSeleccionadas = [];
    }

    // ─── Modal Bulk Posponer ─────────────────────────────────────────────────

    public function openBulkPostponeModal(): void
    {
        if (empty($this->reservasSeleccionadas)) {
            session()->flash('error', 'Selecciona al menos una reserva.');
            return;
        }
        $this->showBulkPostponeModal  = true;
        $this->bulkPostponeFecha      = '';
        $this->bulkPostponeHoraInicio = '';
        $this->bulkPostponeStep       = 'seleccion';
        $this->bulkResultados         = [];
        $this->dispatch('open-bulk-postpone-modal');
    }

    public function closeBulkPostponeModal(): void
    {
        $this->showBulkPostponeModal  = false;
        $this->bulkPostponeFecha      = '';
        $this->bulkPostponeHoraInicio = '';
        $this->bulkPostponeStep       = 'seleccion';
        $this->bulkResultados         = [];
        $this->dispatch('close-bulk-postpone-modal');
    }

    public function posponerEnMasa(): void
    {
        $this->validate([
            'bulkPostponeFecha'      => 'required|date|after_or_equal:today',
            'bulkPostponeHoraInicio' => 'required|date_format:H:i',
        ], [
            'bulkPostponeFecha.required'       => 'Debes elegir una nueva fecha.',
            'bulkPostponeFecha.after_or_equal' => 'La fecha no puede ser en el pasado.',
            'bulkPostponeHoraInicio.required'  => 'Debes elegir una nueva hora.',
            'bulkPostponeHoraInicio.date_format' => 'Formato inválido (HH:MM).',
        ]);

        $user     = auth()->user();
        $sucursal = \App\Models\Sucursal::find($user->sucursal_id);
        if (!$sucursal) {
            session()->flash('error', 'No se pudo determinar la sucursal.');
            return;
        }

        $resultados = $this->reservaService->posponerReservasEnMasa(
            $this->reservasSeleccionadas,
            $this->bulkPostponeFecha,
            $this->bulkPostponeHoraInicio,
            $sucursal
        );

        $this->bulkResultados   = $resultados;
        $this->bulkPostponeStep = 'resultados';
        $this->reservasSeleccionadas = [];
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
            ->where('sucursal_id', $sucursal_id);

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
