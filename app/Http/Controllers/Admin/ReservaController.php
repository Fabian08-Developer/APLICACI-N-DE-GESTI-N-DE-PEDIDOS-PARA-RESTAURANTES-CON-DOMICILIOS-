<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservaMesa;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    public function __construct(private readonly ReservaService $reservaService) {}

    /**
     * GET /admin/reservas/crear
     * Formulario para crear una reserva desde el panel de Administración.
     */
    public function crear()
    {
        $sucursal = auth()->user()->sucursal;
        
        if (!$sucursal) {
            return redirect()->route('admin.dashboard')->with('error', 'Debes estar asignado a una sucursal para crear reservas.');
        }

        $mesas = \App\Models\Mesa::where('sucursal_id', $sucursal->id)->orderBy('numero')->get();

        $montoDeposito    = $this->reservaService->montoDeposito($sucursal);
        $requiereDeposito = $this->reservaService->requiereDeposito($sucursal);
        $duracionTurno    = $this->reservaService->duracionTurno($sucursal);

        return view('admin.reservas.crear', compact(
            'sucursal', 'mesas', 'montoDeposito', 'requiereDeposito', 'duracionTurno'
        ));
    }

    /**
     * POST /admin/reservas
     * Crear una reserva desde el panel del Administrador.
     */
    public function store(Request $request)
    {
        $sucursal = auth()->user()->sucursal;

        if (!$sucursal) {
            return redirect()->route('admin.dashboard')->with('error', 'Debes estar asignado a una sucursal.');
        }

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

            // Desde el panel del administrador, si se indica que el depósito ya fue pagado en efectivo
            if ($request->boolean('deposito_pagado_efectivo') && $reserva->monto_deposito > 0) {
                $this->reservaService->procesarPagoDeposito($reserva, [
                    'metodo'     => 'efectivo',
                    'referencia' => 'Pago en efectivo — registrado por ' . auth()->user()->nombre,
                ], $sucursal);
            }

            return redirect()->route('admin.reservas.index')
                ->with('exito', "Reserva {$reserva->codigo_reserva} creada exitosamente.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }
}
