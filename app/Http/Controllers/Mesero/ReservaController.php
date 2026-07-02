<?php

namespace App\Http\Controllers\Mesero;

use App\Http\Controllers\Controller;
use App\Models\ReservaMesa;
use App\Services\ReservaService;
use App\Traits\BelongsToSucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    public function __construct(private readonly ReservaService $reservaService) {}

    /**
     * GET /mesero/reservas
     * Lista de reservas del día para el mesero.
     */
    public function index(Request $request)
    {
        $sucursal = auth()->user()->sucursal;
        $fecha    = $request->fecha ?? today()->toDateString();

        $reservas = ReservaMesa::where('sucursal_id', $sucursal->id)
            ->where('fecha_reserva', $fecha)
            ->with(['mesas'])
            ->orderBy('hora_inicio')
            ->get();

        $resumen = [
            'total'       => $reservas->count(),
            'confirmadas' => $reservas->where('estado.value', 'confirmada')->count(),
            'pendientes'  => $reservas->whereIn('estado.value', ['pendiente_pago', 'pendiente'])->count(),
            'llegaron'    => $reservas->where('estado.value', 'cliente_llego')->count(),
        ];

        return view('mesero.reservas.index', compact('reservas', 'resumen', 'fecha', 'sucursal'));
    }

    /**
     * POST /mesero/reservas/{id}/confirmar
     * Confirmar una reserva manualmente.
     */
    public function confirmar(string $id)
    {
        $reserva  = ReservaMesa::where('sucursal_id', auth()->user()->sucursal_id)->findOrFail($id);
        $sucursal = auth()->user()->sucursal;

        try {
            $this->reservaService->confirmarReserva($reserva, $sucursal);
            return back()->with('exito', "Reserva {$reserva->codigo_reserva} confirmada exitosamente.");
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    /**
     * POST /mesero/reservas/{id}/check-in
     * Registrar la llegada del cliente.
     */
    public function checkIn(string $id)
    {
        $reserva  = ReservaMesa::where('sucursal_id', auth()->user()->sucursal_id)->findOrFail($id);
        $meseroId = auth()->id();

        try {
            $sesion = $this->reservaService->registrarCheckIn($reserva, $meseroId);
            $numerosMesas = $reserva->mesas->pluck('numero')->join(', ');
            return back()->with('exito', "Check-in registrado. Mesas: {$numerosMesas} ahora están ocupadas. Sesión iniciada.");
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    /**
     * POST /mesero/reservas/{id}/cancelar
     * Cancelar una reserva desde el panel del mesero.
     */
    public function cancelar(Request $request, string $id)
    {
        $reserva  = ReservaMesa::where('sucursal_id', auth()->user()->sucursal_id)->findOrFail($id);
        $sucursal = auth()->user()->sucursal;

        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        try {
            $this->reservaService->cancelarReserva($reserva, $request->motivo, 'restaurante', $sucursal);
            return back()->with('exito', "Reserva {$reserva->codigo_reserva} cancelada.");
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    /**
     * POST /mesero/reservas/{id}/aprobar-deposito
     * Aprobar manualmente el depósito de una reserva.
     */
    public function aprobarDeposito(Request $request, string $id)
    {
        $reserva  = ReservaMesa::where('sucursal_id', auth()->user()->sucursal_id)
            ->with('pagosDeposito')
            ->findOrFail($id);

        $sucursal = auth()->user()->sucursal;

        $request->validate([
            'referencia' => 'required|string|max:255',
        ]);

        $pago = $reserva->pagosDeposito()
            ->where('estado', \App\Models\PagoReserva::ESTADO_PENDIENTE)
            ->latest('creado_en')
            ->first();

        if (!$pago) {
            return back()->with('error', 'No hay un pago pendiente para esta reserva.');
        }

        try {
            $this->reservaService->aprobarPagoDeposito($pago, $request->referencia, $sucursal);
            return back()->with('exito', "Depósito aprobado. Reserva {$reserva->codigo_reserva} confirmada.");
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    /**
     * GET /mesero/reservas/crear
     * Formulario para crear una reserva desde el POS del mesero.
     */
    public function crear()
    {
        $sucursal = auth()->user()->sucursal;
        $mesas    = \App\Models\Mesa::where('sucursal_id', $sucursal->id)->orderBy('numero')->get();

        $montoDeposito    = $this->reservaService->montoDeposito($sucursal);
        $requiereDeposito = $this->reservaService->requiereDeposito($sucursal);
        $duracionTurno    = $this->reservaService->duracionTurno($sucursal);

        return view('mesero.reservas.crear', compact(
            'sucursal', 'mesas', 'montoDeposito', 'requiereDeposito', 'duracionTurno'
        ));
    }

    /**
     * POST /mesero/reservas
     * Crear una reserva desde el panel del mesero.
     */
    public function store(Request $request)
    {
        $sucursal = auth()->user()->sucursal;

        $datos = $request->validate([
            'nombre_cliente'   => 'required|string|max:150',
            'telefono_cliente' => 'required|string|max:30',
            'correo_cliente'   => 'required|email|max:150',
            'numero_personas'  => 'required|integer|min:1',
            'fecha_reserva'    => 'required|date',
            'hora_inicio'      => 'required|date_format:H:i',
            'mesas_ids'        => 'nullable|array',
            'mesas_ids.*'      => 'uuid',
            'notas_cliente'    => 'nullable|string|max:500',
            'notas_internas'   => 'nullable|string|max:500',
        ]);

        try {
            $reserva = $this->reservaService->crearReserva($sucursal, $datos);

            // Desde el panel del mesero, si se indica que el depósito ya fue pagado en efectivo
            if ($request->boolean('deposito_pagado_efectivo') && $reserva->monto_deposito > 0) {
                $this->reservaService->procesarPagoDeposito($reserva, [
                    'metodo'     => 'efectivo',
                    'referencia' => 'Pago en recepción — registrado por ' . auth()->user()->nombre,
                ], $sucursal);
            }

            return redirect()->route('mesero.reservas.index')
                ->with('exito', "Reserva {$reserva->codigo_reserva} creada exitosamente.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

}
