<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\PagoReserva;
use App\Models\ReservaMesa;
use App\Models\Sucursal;
use App\Scopes\TenantScope;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    public function __construct(private readonly ReservaService $reservaService) {}

    // ─── Formulario público de reserva ────────────────────────────

    /**
     * GET /s/{slug}/reservar
     * Muestra el formulario de reserva para una sucursal específica.
     */
    public function formulario(string $slug)
    {
        $sucursal = Sucursal::where('slug', $slug)
            ->where('activo', true)
            ->firstOrFail();

        TenantScope::setTenantId($sucursal->id);

        // Verificar que la sucursal tiene reservas activas
        $config = is_string($sucursal->configuracion)
            ? json_decode($sucursal->configuracion, true)
            : ($sucursal->configuracion ?? []);

        if (isset($config['reservas_activas']) && !$config['reservas_activas']) {
            return view('cliente.reservas.no-disponible', compact('sucursal'));
        }

        $mesas = Mesa::where('sucursal_id', $sucursal->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'capacidad']);

        $montoDeposito    = $this->reservaService->montoDeposito($sucursal);
        $requiereDeposito = $this->reservaService->requiereDeposito($sucursal);
        $duracionTurno    = $this->reservaService->duracionTurno($sucursal);
        $anticipacionMin  = $this->reservaService->anticipacionMinima($sucursal);
        $horizonteDias    = $this->reservaService->horizonteDias($sucursal);

        return view('cliente.reservas.formulario', compact(
            'sucursal',
            'mesas',
            'montoDeposito',
            'requiereDeposito',
            'duracionTurno',
            'anticipacionMin',
            'horizonteDias'
        ));
    }

    /**
     * GET /s/{slug}/reservar/slots  (AJAX)
     * Devuelve slots disponibles para una fecha y número de personas.
     */
    public function slots(Request $request, string $slug)
    {
        $sucursal = Sucursal::where('slug', $slug)->where('activo', true)->firstOrFail();

        TenantScope::setTenantId($sucursal->id);

        $request->validate([
            'fecha'         => 'required|date|after_or_equal:today',
            'numero_personas' => 'required|integer|min:1',
            'mesa_id'       => 'nullable|uuid',
        ]);

        $fecha    = $request->fecha;
        $personas = (int) $request->numero_personas;
        $duracion = $this->reservaService->duracionTurno($sucursal);

        $apertura  = $sucursal->hora_apertura ?? '09:00:00';
        $cierre    = $sucursal->hora_cierre   ?? '22:00:00';
        $anticipacionMin = $this->reservaService->anticipacionMinima($sucursal);

        if ($request->filled('mesas_ids')) {
            $mesasIds = $request->input('mesas_ids');
            $mesas = Mesa::where('sucursal_id', $sucursal->id)
                ->whereIn('id', $mesasIds)
                ->get();

            if ($mesas->isEmpty()) {
                throw ValidationException::withMessages(['mesas_ids' => 'Mesa seleccionada no válida.']);
            }
            $slots = $this->reservaService->slotsDisponiblesParaMesas($mesas, $fecha, $sucursal);
        } else {
            // Slots donde existe al menos una mesa disponible con capacidad
            $slots = $this->slotsGenerales($sucursal, $fecha, $duracion, $apertura, $cierre, $anticipacionMin, $personas);
        }

        return response()->json(['slots' => $slots]);
    }

    /**
     * POST /s/{slug}/reservar
     * Procesa el formulario y crea la reserva.
     */
    public function crear(Request $request, string $slug)
    {
        $sucursal = Sucursal::where('slug', $slug)->where('activo', true)->firstOrFail();

        TenantScope::setTenantId($sucursal->id);

        $datos = $request->validate([
            'nombre_cliente'   => 'required|string|max:150',
            'telefono_cliente' => 'required|string|max:30',
            'correo_cliente'   => 'required|email|max:150',
            'numero_personas'  => 'required|integer|min:1|max:50',
            'fecha_reserva'    => 'required|date|after_or_equal:today',
            'hora_inicio'      => 'required|date_format:H:i',
            'mesas_ids'        => 'nullable|array',
            'mesas_ids.*'      => 'uuid',
            'notas_cliente'    => 'nullable|string|max:500',
        ]);

        try {
            $reserva = $this->reservaService->crearReserva($sucursal, $datos);

            // Si requiere depósito: redirigir a página de pago
            if ($reserva->monto_deposito > 0 && !$reserva->deposito_pagado) {
                return redirect()->route('cliente.reservas.deposito', [
                    'slug'   => $slug,
                    'codigo' => $reserva->codigo_reserva,
                ])->with('info', 'Reserva creada. Por favor completa el pago del depósito.');
            }

            return redirect()->route('cliente.reservas.confirmada', [
                'codigo' => $reserva->codigo_reserva,
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * GET /reserva/{codigo}/deposito
     * Página de pago del depósito.
     */
    public function deposito(string $slug, string $codigo)
    {
        $reserva  = ReservaMesa::withoutGlobalScopes()->where('codigo_reserva', $codigo)->firstOrFail();
        $sucursal = $reserva->sucursal;

        TenantScope::setTenantId($sucursal->id);

        if ($reserva->deposito_pagado) {
            return redirect()->route('cliente.reservas.confirmada', ['codigo' => $codigo]);
        }

        return view('cliente.reservas.deposito', compact('reserva', 'sucursal'));
    }

    /**
     * POST /reserva/{codigo}/deposito
     * Procesa el pago del depósito.
     */
    public function procesarDeposito(Request $request, string $slug, string $codigo)
    {
        $reserva  = ReservaMesa::withoutGlobalScopes()->where('codigo_reserva', $codigo)->firstOrFail();
        $sucursal = $reserva->sucursal;

        TenantScope::setTenantId($sucursal->id);

        $datos = $request->validate([
            'metodo'          => 'required|in:efectivo,nequi,transferencia',
            'referencia'      => 'nullable|string|max:255',
            'nequi_telefono'  => 'nullable|string|max:20',
            'nequi_correo'    => 'nullable|email|max:150',
            'notas'           => 'nullable|string|max:500',
        ]);

        try {
            $this->reservaService->procesarPagoDeposito($reserva, $datos, $sucursal);

            return redirect()->route('cliente.reservas.confirmada', ['codigo' => $codigo])
                ->with('exito', 'Depósito registrado exitosamente. ¡Tu reserva está en proceso!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * GET /reserva/{codigo}
     * Página de confirmación de la reserva.
     */
    public function confirmada(string $codigo)
    {
        $reserva = ReservaMesa::withoutGlobalScopes()->where('codigo_reserva', $codigo)
            ->with(['sucursal', 'mesas'])
            ->firstOrFail();

        TenantScope::setTenantId($reserva->sucursal_id);

        return view('cliente.reservas.confirmada', compact('reserva'));
    }

    /**
     * GET /reserva/{codigo}/cancelar
     * Formulario de cancelación por el cliente.
     */
    public function cancelarFormulario(string $codigo)
    {
        $reserva = ReservaMesa::withoutGlobalScopes()->where('codigo_reserva', $codigo)
            ->with(['sucursal', 'mesas'])
            ->firstOrFail();

        TenantScope::setTenantId($reserva->sucursal_id);

        if ($reserva->estado->esFinal()) {
            return view('cliente.reservas.ya-cancelada', compact('reserva'));
        }

        $sucursal = $reserva->sucursal;
        $limiteMinutos = $this->reservaService->limiteCancelacion($sucursal);

        if (!$reserva->clientePuedeCancelar($limiteMinutos)) {
            return view('cliente.reservas.cancelacion-bloqueada', compact('reserva', 'limiteMinutos'));
        }

        return view('cliente.reservas.cancelar', compact('reserva'));
    }

    /**
     * POST /reserva/{codigo}/cancelar
     * Procesa la cancelación por el cliente.
     */
    public function cancelar(Request $request, string $codigo)
    {
        $reserva  = ReservaMesa::withoutGlobalScopes()->where('codigo_reserva', $codigo)->firstOrFail();
        $sucursal = $reserva->sucursal;

        TenantScope::setTenantId($sucursal->id);

        $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        try {
            $this->reservaService->cancelarReserva(
                $reserva,
                $request->motivo ?? 'Cancelado por el cliente.',
                'cliente',
                $sucursal
            );

            return view('cliente.reservas.cancelacion-exitosa', compact('reserva'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Calcula slots generales donde hay al menos una mesa disponible.
     */
    private function slotsGenerales(
        Sucursal $sucursal,
        string $fecha,
        int $duracion,
        string $apertura,
        string $cierre,
        int $anticipacionMin,
        int $personas
    ): array {
        $slots   = [];
        $cursor  = \Carbon\Carbon::parse($fecha . ' ' . $apertura);
        $finDia  = \Carbon\Carbon::parse($fecha . ' ' . $cierre);
        $ahora   = now()->addMinutes($anticipacionMin);

        while ($cursor->copy()->addMinutes($duracion)->lte($finDia)) {
            $slotFin = $cursor->copy()->addMinutes($duracion);

            $hayMesa = $cursor->gte($ahora)
                ? $this->reservaService->mesasDisponiblesParaSlot(
                    $sucursal,
                    $fecha,
                    $cursor->format('H:i'),
                    $slotFin->format('H:i'),
                    $personas
                )->isNotEmpty()
                : false;

            $slots[] = [
                'hora_inicio' => $cursor->format('H:i'),
                'hora_fin'    => $slotFin->format('H:i'),
                'disponible'  => $hayMesa,
            ];

            $cursor->addMinutes($duracion);
        }

        return $slots;
    }
}
