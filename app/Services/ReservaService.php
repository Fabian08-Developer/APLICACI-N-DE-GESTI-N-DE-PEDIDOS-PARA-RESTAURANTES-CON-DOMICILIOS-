<?php

namespace App\Services;

use App\Enums\EstadoReserva;
use App\Jobs\EnviarRecordatorioReservaJob;
use App\Mail\ReservaConfirmadaMail;
use App\Mail\ReservaCanceladaMail;
use App\Mail\ReservaRecibidaMail;
use App\Mail\ReservaExpiradaMail;
use App\Mail\ComprobantePagoReservaMail;
use App\Models\Mesa;
use App\Services\WebPushService;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\ReservaMesa;
use App\Models\SesionCliente;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ReservaService
{
    // ─── Configuración por defecto (puede sobreescribirse desde sucursal.configuracion) ───

    const DEFAULT_DURACION_TURNO_MIN      = 90;
    const DEFAULT_TOLERANCIA_LLEGADA_MIN  = 15;
    const DEFAULT_ANTICIPACION_MIN_MIN    = 30;
    const DEFAULT_HORIZONTE_DIAS          = 30;
    const DEFAULT_AUTO_CONFIRMAR          = false; // Con depósito, confirmación es manual por defecto
    const DEFAULT_LIMITE_CANCELACION_MIN  = 60;
    const DEFAULT_MONTO_DEPOSITO          = 20000; // $20.000 COP por defecto
    const DEFAULT_REQUIERE_DEPOSITO       = true;

    // ─── Helpers de configuración ─────────────────────────────────

    /**
     * Obtiene un parámetro de configuración de la sucursal con fallback al default.
     */
    private function config(Sucursal $sucursal, string $clave, mixed $default): mixed
    {
        $config = $sucursal->configuracion ?? [];
        if (is_string($config)) {
            $config = json_decode($config, true) ?? [];
        }
        return $config["reservas_{$clave}"] ?? $default;
    }

    public function duracionTurno(Sucursal $sucursal): int
    {
        return (int) $this->config($sucursal, 'duracion_turno_minutos', self::DEFAULT_DURACION_TURNO_MIN);
    }

    public function toleranciaLlegada(Sucursal $sucursal): int
    {
        return (int) $this->config($sucursal, 'tolerancia_llegada_minutos', self::DEFAULT_TOLERANCIA_LLEGADA_MIN);
    }

    public function anticipacionMinima(Sucursal $sucursal): int
    {
        // Regla de negocio fija: mínimo 30 minutos de anticipación, sin excepciones.
        $configurado = (int) $this->config($sucursal, 'anticipacion_minima_minutos', self::DEFAULT_ANTICIPACION_MIN_MIN);
        return max(self::DEFAULT_ANTICIPACION_MIN_MIN, $configurado);
    }

    public function horizonteDias(Sucursal $sucursal): int
    {
        return (int) $this->config($sucursal, 'horizonte_dias', self::DEFAULT_HORIZONTE_DIAS);
    }

    public function autoConfirmar(Sucursal $sucursal): bool
    {
        return (bool) $this->config($sucursal, 'auto_confirmar', self::DEFAULT_AUTO_CONFIRMAR);
    }

    public function limiteCancelacion(Sucursal $sucursal): int
    {
        return (int) $this->config($sucursal, 'limite_cancelacion_minutos', self::DEFAULT_LIMITE_CANCELACION_MIN);
    }

    public function montoDeposito(Sucursal $sucursal): float
    {
        return (float) $this->config($sucursal, 'monto_deposito', self::DEFAULT_MONTO_DEPOSITO);
    }

    public function requiereDeposito(Sucursal $sucursal): bool
    {
        return (bool) $this->config($sucursal, 'requiere_deposito', self::DEFAULT_REQUIERE_DEPOSITO);
    }

    // ─── Verificación de disponibilidad ───────────────────────────

    /**
     * Verifica si una mesa está disponible para un slot de tiempo dado.
     * Lanza ValidationException si no está disponible.
     *
     * @param  Mesa    $mesa
     * @param  string  $fecha      Formato: Y-m-d
     * @param  string  $horaInicio Formato: H:i
     * @param  string  $horaFin    Formato: H:i
     * @param  string|null $excluirReservaId  Para edición: excluye la reserva actual del chequeo.
     */
    public function verificarDisponibilidad(
        array $mesasIds,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        ?string $excluirReservaId = null
    ): void {
        $query = ReservaMesa::whereHas('mesas', function($q) use ($mesasIds) {
                $q->whereIn('mesas.id', $mesasIds);
            })
            ->where('fecha_reserva', $fecha)
            ->activas()
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->where('hora_inicio', '<', $horaFin)
                  ->where('hora_fin',   '>', $horaInicio);
            });

        if ($excluirReservaId) {
            $query->where('id', '!=', $excluirReservaId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'Una o más de las mesas seleccionadas ya tiene una reserva en ese horario. Por favor elige otra hora u otra mesa.',
            ]);
        }
    }

    /**
     * Devuelve los slots disponibles para una mesa en una fecha dada.
     * Útil para mostrar al cliente qué horas puede reservar.
     *
     * @return array  Lista de ['hora_inicio' => '...', 'hora_fin' => '...', 'disponible' => bool]
     */
    public function slotsDisponibles(Mesa $mesa, string $fecha, Sucursal $sucursal): array
    {
        $duracion = $this->duracionTurno($sucursal);
        $apertura = $sucursal->hora_apertura ?? '09:00:00';
        $cierre   = $sucursal->hora_cierre   ?? '22:00:00';
        $tz       = $sucursal->timezone ?? config('app.timezone', 'America/Bogota');

        $reservasExistentes = ReservaMesa::whereHas('mesas', function($q) use ($mesa) {
                $q->where('mesas.id', $mesa->id);
            })
            ->where('fecha_reserva', $fecha)
            ->activas()
            ->get(['hora_inicio', 'hora_fin']);

        $slots = [];
        $cursor = Carbon::parse($fecha . ' ' . $apertura, $tz);
        $fin    = Carbon::parse($fecha . ' ' . $cierre, $tz);

        // Anticipación mínima: no mostrar slots ya expirados según timezone de la sucursal
        $ahora = now($tz)->addMinutes($this->anticipacionMinima($sucursal));

        while ($cursor->copy()->addMinutes($duracion)->lte($fin)) {
            $slotFin = $cursor->copy()->addMinutes($duracion);

            // Verificar si hay solapamiento con reserva existente
            $hayConflicto = $reservasExistentes->contains(function ($r) use ($cursor, $slotFin) {
                $rIni = Carbon::parse($r->hora_inicio);
                $rFin = Carbon::parse($r->hora_fin);
                return $cursor->lt($rFin) && $slotFin->gt($rIni);
            });

            $disponible = !$hayConflicto && $cursor->gte($ahora);

            $slots[] = [
                'hora_inicio' => $cursor->format('H:i'),
                'hora_fin'    => $slotFin->format('H:i'),
                'disponible'  => $disponible,
            ];

            $cursor->addMinutes($duracion);
        }

        return $slots;
    }

    /**
     * Devuelve los IDs de las mesas de una sucursal ocupadas en un slot de tiempo dado.
     */
    public function mesasOcupadasEnSlot(
        Sucursal $sucursal,
        string $fecha,
        string $horaInicio,
        string $horaFin
    ): array {
        return DB::table('reserva_mesas')
            ->join('reservas', 'reserva_mesas.reserva_id', '=', 'reservas.id')
            ->where('reservas.sucursal_id', $sucursal->id)
            ->where('reservas.fecha_reserva', $fecha)
            ->whereIn('reservas.estado', [EstadoReserva::PENDIENTE->value, EstadoReserva::CONFIRMADA->value, EstadoReserva::CLIENTE_LLEGO->value])
            ->where('reservas.hora_inicio', '<', $horaFin)
            ->where('reservas.hora_fin', '>', $horaInicio)
            ->pluck('reserva_mesas.mesa_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Devuelve las mesas de una sucursal disponibles para un slot de tiempo dado.
     */
    public function mesasDisponiblesParaSlot(
        Sucursal $sucursal,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        int $personas = 1
    ): \Illuminate\Database\Eloquent\Collection {
        $mesasOcupadas = DB::table('reserva_mesas')
            ->join('reservas', 'reserva_mesas.reserva_id', '=', 'reservas.id')
            ->where('reservas.sucursal_id', $sucursal->id)
            ->where('reservas.fecha_reserva', $fecha)
            ->whereIn('reservas.estado', [EstadoReserva::PENDIENTE->value, EstadoReserva::CONFIRMADA->value, EstadoReserva::CLIENTE_LLEGO->value])
            ->where('reservas.hora_inicio', '<', $horaFin)
            ->where('reservas.hora_fin', '>', $horaInicio)
            ->pluck('reserva_mesas.mesa_id');

        return Mesa::where('sucursal_id', $sucursal->id)
            ->where('capacidad', '>=', $personas)
            ->whereNotIn('id', $mesasOcupadas)
            ->orderBy('capacidad')
            ->orderBy('numero')
            ->get();
    }

    private function asignarMesasAutomaticamente(Sucursal $sucursal, string $fecha, string $horaInicio, string $horaFin, int $personas): array
    {
        $disponibles = $this->mesasDisponiblesParaSlot($sucursal, $fecha, $horaInicio, $horaFin, 1);

        $mesaIdeal = $disponibles->where('capacidad', '>=', $personas)->first();
        if ($mesaIdeal) {
            return [$mesaIdeal->id];
        }

        $mesasIds = [];
        $capacidadAcumulada = 0;
        foreach ($disponibles->sortByDesc('capacidad') as $mesa) {
            $mesasIds[] = $mesa->id;
            $capacidadAcumulada += $mesa->capacidad;
            if ($capacidadAcumulada >= $personas) {
                return $mesasIds;
            }
        }

        throw ValidationException::withMessages([
            'hora_inicio' => 'No hay suficientes mesas disponibles para el horario y cantidad de personas solicitados.',
        ]);
    }

    // ─── Crear reserva ────────────────────────────────────────────

    /**
     * Crea una nueva reserva con todas las validaciones de negocio.
     *
     * @param  array $datos {
     *   sucursal_id, mesa_id (nullable), nombre_cliente, telefono_cliente,
     *   correo_cliente, numero_personas, fecha_reserva, hora_inicio, notas_cliente
     * }
     */
    public function crearReserva(Sucursal $sucursal, array $datos): ReservaMesa
    {
        return DB::transaction(function () use ($sucursal, $datos) {

            // 1. Validar que el módulo está activo
            if (!$this->config($sucursal, 'activas', true)) {
                throw ValidationException::withMessages([
                    'general' => 'Las reservas no están habilitadas en este restaurante.',
                ]);
            }

            // 2. Validar anticipación mínima y horizonte máximo
            $tz              = $sucursal->timezone ?? config('app.timezone', 'America/Bogota');
            $fechaHoraInicio = Carbon::parse($datos['fecha_reserva'] . ' ' . $datos['hora_inicio'], $tz);
            $anticipacion    = $this->anticipacionMinima($sucursal);
            $horizonte       = $this->horizonteDias($sucursal);

            if ($fechaHoraInicio->lt(now($tz)->addMinutes($anticipacion))) {
                throw ValidationException::withMessages([
                    'hora_inicio' => "Debes reservar con al menos {$anticipacion} minutos de anticipación.",
                ]);
            }

            if ($fechaHoraInicio->gt(now($tz)->addDays($horizonte))) {
                throw ValidationException::withMessages([
                    'fecha_reserva' => "Solo puedes reservar hasta {$horizonte} días en el futuro.",
                ]);
            }

            // 3. Calcular hora de fin
            $duracion   = $this->duracionTurno($sucursal);
            $horaFin    = $fechaHoraInicio->copy()->addMinutes($duracion)->format('H:i');

            // 4. Resolución híbrida de mesa
            $mesasIds = $datos['mesas_ids'] ?? [];
            $mesasIds = array_filter((array) $mesasIds);

            if (!empty($mesasIds)) {
                // Lock pesimista: bloquea las filas de mesa durante la transacción
                // para prevenir race conditions cuando dos requests llegan al mismo tiempo.
                $mesas = Mesa::where('sucursal_id', $sucursal->id)
                             ->whereIn('id', $mesasIds)
                             ->lockForUpdate()
                             ->get();

                $capacidadTotal = $mesas->sum('capacidad');
                if ($capacidadTotal < $datos['numero_personas']) {
                    throw ValidationException::withMessages([
                        'numero_personas' => "Las mesas seleccionadas tienen capacidad total para {$capacidadTotal} personas.",
                    ]);
                }

                $this->verificarDisponibilidad($mesasIds, $datos['fecha_reserva'], $datos['hora_inicio'], $horaFin);
            } else {
                // Asignación automática
                $mesasIds = $this->asignarMesasAutomaticamente(
                    $sucursal,
                    $datos['fecha_reserva'],
                    $datos['hora_inicio'],
                    $horaFin,
                    $datos['numero_personas']
                );
            }

            // 5. Calcular depósito (por cada mesa asignada/seleccionada)
            $requiereDeposito = $this->requiereDeposito($sucursal);
            $cantidadMesas    = max(1, count($mesasIds));
            $montoDeposito    = $requiereDeposito ? ($this->montoDeposito($sucursal) * $cantidadMesas) : 0;

            // 6. Estado inicial según si hay depósito requerido
            $estadoInicial = $requiereDeposito
                ? EstadoReserva::PENDIENTE_PAGO->value
                : EstadoReserva::PENDIENTE->value;

            // 7. Crear la reserva
            $reserva = ReservaMesa::create([
                'sucursal_id'      => $sucursal->id,
                'codigo_reserva'   => ReservaMesa::generarCodigo(),
                'nombre_cliente'   => $datos['nombre_cliente'],
                'telefono_cliente' => $datos['telefono_cliente'],
                'correo_cliente'   => $datos['correo_cliente'],
                'numero_personas'  => $datos['numero_personas'],
                'notas_cliente'    => $datos['notas_cliente'] ?? null,
                'fecha_reserva'    => $datos['fecha_reserva'],
                'hora_inicio'      => $datos['hora_inicio'],
                'hora_fin'         => $horaFin,
                'estado'           => $estadoInicial,
                'monto_deposito'   => $montoDeposito,
                'deposito_pagado'  => false,
            ]);

            $reserva->mesas()->sync($mesasIds);

            // 8. Si NO requiere depósito: auto-confirmar o dejar pendiente
            if (!$requiereDeposito) {
                if ($this->autoConfirmar($sucursal)) {
                    $this->confirmarReserva($reserva, $sucursal);
                } else {
                    $this->notificarAdminNuevaReserva($reserva, $sucursal);
                }
            }
            // Si requiere depósito, el flujo continua cuando el cliente paga
            // (ver: procesarPagoDeposito)

            $reservaFresh = $reserva->fresh();

            // Enviar correo de "Reserva Recibida" con el comprobante y detalles.
            // Se encola para liberar la transacción inmediatamente (no bloquear por SMTP).
            if ($reservaFresh->estado === EstadoReserva::PENDIENTE_PAGO || $reservaFresh->estado === EstadoReserva::PENDIENTE) {
                try {
                    Mail::to($reservaFresh->correo_cliente)->queue(new ReservaRecibidaMail($reservaFresh));
                } catch (\Exception $e) {
                    logger()->error('Error encolando email de reserva recibida', [
                        'reserva_id' => $reservaFresh->id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }

            return $reservaFresh;
        });
    }

    // ─── Confirmar reserva ────────────────────────────────────────

    /**
     * Confirma una reserva y envía notificaciones al cliente.
     */
    public function confirmarReserva(ReservaMesa $reserva, ?Sucursal $sucursal = null): void
    {
        if (!$reserva->estado->puedeTransicionarA(EstadoReserva::CONFIRMADA)) {
            throw ValidationException::withMessages([
                'estado' => 'La reserva no puede confirmarse desde su estado actual.',
            ]);
        }

        // Bloquear si el depósito es requerido y no ha sido pagado
        if ($reserva->monto_deposito > 0 && !$reserva->deposito_pagado) {
            throw ValidationException::withMessages([
                'deposito' => 'No se puede confirmar la reserva sin que el depósito haya sido pagado.',
            ]);
        }

        $reserva->confirmar();

        // Enviar email de confirmación (encolado para no bloquear el flujo principal)
        try {
            Mail::to($reserva->correo_cliente)
                ->queue(new ReservaConfirmadaMail($reserva));
        } catch (\Exception $e) {
            logger()->error('Error encolando email de confirmación de reserva', [
                'reserva_id' => $reserva->id,
                'error'      => $e->getMessage(),
            ]);
        }

        // Programar recordatorio 2 horas antes
        $horaRecordatorio = $reserva->inicio->subHours(2);
        if ($horaRecordatorio->isFuture()) {
            EnviarRecordatorioReservaJob::dispatch($reserva->id)
                ->delay($horaRecordatorio);
        }

        // Notificación interna 30 min antes (para mesero/admin)
        $this->programarAlertaInterna($reserva);
    }

    // ─── Check-in del cliente ─────────────────────────────────────

    /**
     * Registra la llegada del cliente:
     *  1. Marca la reserva como CLIENTE_LLEGO
     *  2. Crea la SesionCliente (flujo existente)
     *  3. Cambia el estado de la mesa a 'ocupada'
     */
    public function registrarCheckIn(ReservaMesa $reserva, ?string $meseroId = null): SesionCliente
    {
        if (!$reserva->estado->puedeTransicionarA(EstadoReserva::CLIENTE_LLEGO)) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede registrar el check-in en el estado actual de la reserva.',
            ]);
        }

        return DB::transaction(function () use ($reserva, $meseroId) {
            // 1. Actualizar estado de la reserva
            $reserva->registrarLlegada($meseroId);

            // 2. Crear SesionCliente (igual que al escanear QR)
            $sesion = SesionCliente::create([
                'sucursal_id'     => $reserva->sucursal_id,
                'mesa_id'         => $reserva->mesas->first()?->id,
                'mesero_id'       => $meseroId,
                'token'           => \Illuminate\Support\Str::random(48),
                'tipo'            => 'local',
                'nombre_cliente'  => $reserva->nombre_cliente,
                'telefono_cliente'=> $reserva->telefono_cliente,
                'correo_cliente'  => $reserva->correo_cliente,
                'activo'          => true,
                'ultima_actividad_en' => now(),
            ]);

            // 3. Vincular sesión a la reserva
            $reserva->update(['sesion_cliente_id' => $sesion->id]);

            // 4. Cambiar estado de las mesas a 'ocupada'
            foreach ($reserva->mesas as $mesa) {
                $mesa->ocupar();
            }

            return $sesion;
        });
    }

    // ─── Cancelar reserva ─────────────────────────────────────────

    /**
     * Cancela una reserva respetando las reglas de negocio.
     *
     * @param  string $por  'cliente' o 'restaurante'
     */
    public function cancelarReserva(ReservaMesa $reserva, string $motivo, string $por = 'restaurante', ?Sucursal $sucursal = null): void
    {
        if ($reserva->estado->esFinal()) {
            throw ValidationException::withMessages([
                'estado' => 'Esta reserva ya está en un estado final y no puede cancelarse.',
            ]);
        }

        // Validar si el cliente puede cancelar aún
        if ($por === 'cliente' && $sucursal) {
            $limiteMinutos = $this->limiteCancelacion($sucursal);
            if (!$reserva->clientePuedeCancelar($limiteMinutos)) {
                throw ValidationException::withMessages([
                    'tiempo' => "Ya no puedes cancelar esta reserva. El límite es {$limiteMinutos} minutos antes de la hora.",
                ]);
            }
        }

        $reserva->cancelar($motivo, $por);

        // Enviar email de cancelación (encolado)
        try {
            Mail::to($reserva->correo_cliente)
                ->queue(new ReservaCanceladaMail($reserva));
        } catch (\Exception $e) {
            logger()->error('Error encolando email de cancelación de reserva', [
                'reserva_id' => $reserva->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    // ─── Posponer / Reprogramar reserva ──────────────────────────

    /**
     * Valida la nueva fecha/hora y verifica si las mesas actuales tienen conflicto.
     * NO modifica nada en base de datos — es solo lectura.
     *
     * @return array{conflicto: bool, nuevaHoraFin: string, mesasActualesIds: array, mesasConflicto: array, mesasAlternativas: mixed}
     * @throws ValidationException  Si la fecha/hora es inválida.
     */
    public function verificarConflictoPostponer(
        ReservaMesa $reserva,
        string $nuevaFecha,
        string $nuevaHoraInicio,
        Sucursal $sucursal
    ): array {
        if ($reserva->estado->esFinal()) {
            throw ValidationException::withMessages([
                'estado' => 'Esta reserva está en un estado final y no puede reprogramarse.',
            ]);
        }

        $tz             = $sucursal->timezone ?? config('app.timezone', 'America/Bogota');
        $nuevaFechaHora = Carbon::parse($nuevaFecha . ' ' . $nuevaHoraInicio, $tz);
        $anticipacion   = $this->anticipacionMinima($sucursal);

        if ($nuevaFechaHora->lt(now($tz)->addMinutes($anticipacion))) {
            throw ValidationException::withMessages([
                'postpone_hora_inicio' => "Debes reprogramar con al menos {$anticipacion} minutos de anticipación.",
            ]);
        }

        $horizonte = $this->horizonteDias($sucursal);
        if ($nuevaFechaHora->gt(now($tz)->addDays($horizonte))) {
            throw ValidationException::withMessages([
                'postpone_fecha' => "Solo puedes reservar hasta {$horizonte} días en el futuro.",
            ]);
        }

        $duracion     = $this->duracionTurno($sucursal);
        $nuevaHoraFin = $nuevaFechaHora->copy()->addMinutes($duracion)->format('H:i');
        $mesasActualesIds = $reserva->mesas->pluck('id')->toArray();

        // Detectar qué mesas actuales tienen conflicto en el nuevo slot
        $mesasConflicto = [];
        if (!empty($mesasActualesIds)) {
            $mesasConflicto = DB::table('reserva_mesas')
                ->join('reservas_mesa', 'reserva_mesas.reserva_id', '=', 'reservas_mesa.id')
                ->where('reservas_mesa.id', '!=', $reserva->id)
                ->where('reservas_mesa.fecha_reserva', $nuevaFecha)
                ->whereIn('reservas_mesa.estado', [
                    \App\Enums\EstadoReserva::PENDIENTE->value,
                    \App\Enums\EstadoReserva::CONFIRMADA->value,
                    \App\Enums\EstadoReserva::CLIENTE_LLEGO->value,
                    \App\Enums\EstadoReserva::PENDIENTE_PAGO->value,
                ])
                ->where('reservas_mesa.hora_inicio', '<', $nuevaHoraFin)
                ->where('reservas_mesa.hora_fin',   '>', $nuevaHoraInicio)
                ->whereIn('reserva_mesas.mesa_id', $mesasActualesIds)
                ->pluck('reserva_mesas.mesa_id')
                ->toArray();
        }

        $hayConflicto = !empty($mesasConflicto);
        $mesasAlternativas = $hayConflicto
            ? $this->mesasDisponiblesParaSlot($sucursal, $nuevaFecha, $nuevaHoraInicio, $nuevaHoraFin, $reserva->numero_personas)
            : collect();

        return [
            'conflicto'         => $hayConflicto,
            'nuevaHoraFin'      => $nuevaHoraFin,
            'mesasActualesIds'  => $mesasActualesIds,
            'mesasConflicto'    => $mesasConflicto,
            'mesasAlternativas' => $mesasAlternativas,
        ];
    }

    /**
     * Guarda la reprogramación con las mesas indicadas (manual o auto-asignadas).
     *
     * @param  array  $mesasIds  IDs de mesas a usar en el nuevo slot.
     * @throws ValidationException
     */
    public function posponerReservaConMesas(
        ReservaMesa $reserva,
        string $nuevaFecha,
        string $nuevaHoraInicio,
        string $nuevaHoraFin,
        array $mesasIds,
        Sucursal $sucursal
    ): void {
        $this->verificarDisponibilidad($mesasIds, $nuevaFecha, $nuevaHoraInicio, $nuevaHoraFin, $reserva->id);

        DB::transaction(function () use ($reserva, $nuevaFecha, $nuevaHoraInicio, $nuevaHoraFin, $mesasIds) {
            $fechaAnterior = $reserva->fecha_reserva->format('Y-m-d');
            $horaAnterior  = $reserva->hora_inicio;

            $reserva->update([
                'fecha_reserva' => $nuevaFecha,
                'hora_inicio'   => $nuevaHoraInicio,
                'hora_fin'      => $nuevaHoraFin,
            ]);

            $reserva->mesas()->sync($mesasIds);

            try {
                Mail::to($reserva->correo_cliente)
                    ->queue(new \App\Mail\ReservaPospuestaMail(
                        $reserva->fresh(['mesas', 'sucursal']),
                        $fechaAnterior,
                        $horaAnterior,
                    ));
            } catch (\Exception $e) {
                logger()->error('Error encolando email de reserva pospuesta', [
                    'reserva_id' => $reserva->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Procesa en masa la reprogramación de un lote de reservas.
     *
     * Por cada reserva:
     *  - Si sus mesas actuales están libres → reprograma conservando mesas.
     *  - Si hay conflicto y hay alternativas → reasigna automáticamente.
     *  - Si hay conflicto y NO hay alternativas → registra como 'conflicto'.
     *
     * @param  array    $reservasIds
     * @param  string   $nuevaFecha         Formato: Y-m-d
     * @param  string   $nuevaHoraInicio    Formato: H:i
     * @param  Sucursal $sucursal
     * @return array{exitosas: int, conflictos: int, errores: int, detalle: array}
     */
    public function posponerReservasEnMasa(
        array $reservasIds,
        string $nuevaFecha,
        string $nuevaHoraInicio,
        Sucursal $sucursal
    ): array {
        $exitosas  = 0;
        $conflictos = 0;
        $errores   = 0;
        $detalle   = [];

        $reservas = ReservaMesa::with('mesas')
            ->whereIn('id', $reservasIds)
            ->where('sucursal_id', $sucursal->id)
            ->get();

        foreach ($reservas as $reserva) {
            try {
                $info = $this->verificarConflictoPostponer($reserva, $nuevaFecha, $nuevaHoraInicio, $sucursal);

                if ($info['conflicto']) {
                    try {
                        $nuevasMesas = $this->asignarMesasAutomaticamente(
                            $sucursal, $nuevaFecha, $nuevaHoraInicio, $info['nuevaHoraFin'], $reserva->numero_personas
                        );
                        $this->posponerReservaConMesas($reserva, $nuevaFecha, $nuevaHoraInicio, $info['nuevaHoraFin'], $nuevasMesas, $sucursal);
                        $exitosas++;
                        $detalle[] = ['id' => $reserva->id, 'codigo' => $reserva->codigo_reserva, 'nombre' => $reserva->nombre_cliente, 'resultado' => 'exitosa_reasignada', 'mensaje' => 'Reprogramada con mesas reasignadas automáticamente.'];
                    } catch (\Exception $e) {
                        $conflictos++;
                        $detalle[] = ['id' => $reserva->id, 'codigo' => $reserva->codigo_reserva, 'nombre' => $reserva->nombre_cliente, 'resultado' => 'conflicto', 'mensaje' => 'No hay mesas disponibles. Requiere atención manual.'];
                    }
                } else {
                    $this->posponerReservaConMesas($reserva, $nuevaFecha, $nuevaHoraInicio, $info['nuevaHoraFin'], $info['mesasActualesIds'], $sucursal);
                    $exitosas++;
                    $detalle[] = ['id' => $reserva->id, 'codigo' => $reserva->codigo_reserva, 'nombre' => $reserva->nombre_cliente, 'resultado' => 'exitosa', 'mensaje' => 'Reprogramada conservando mesas.'];
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                $errores++;
                $detalle[] = ['id' => $reserva->id, 'codigo' => $reserva->codigo_reserva, 'nombre' => $reserva->nombre_cliente, 'resultado' => 'error', 'mensaje' => collect($e->errors())->flatten()->first()];
            } catch (\Exception $e) {
                $errores++;
                $detalle[] = ['id' => $reserva->id, 'codigo' => $reserva->codigo_reserva, 'nombre' => $reserva->nombre_cliente, 'resultado' => 'error', 'mensaje' => 'Error inesperado.'];
                logger()->error('Error en posponer masivo', ['reserva_id' => $reserva->id, 'error' => $e->getMessage()]);
            }
        }

        return compact('exitosas', 'conflictos', 'errores', 'detalle');
    }


    // ─── Procesar No-Shows ────────────────────────────────────────


    /**
     * Detecta y procesa reservas confirmadas que ya expiraron sin check-in.
     * Debe llamarse desde un Job programado cada 5 minutos.
     *
     * @return int  Cantidad de reservas marcadas como NO_SHOW.
     */
    public function procesarNoShows(Sucursal $sucursal): int
    {
        $tolerancia = $this->toleranciaLlegada($sucursal);

        $reservasExpiradas = ReservaMesa::where('sucursal_id', $sucursal->id)
            ->conNoShowPendiente($tolerancia)
            ->get();

        foreach ($reservasExpiradas as $reserva) {
            $reserva->marcarNoShow();

            // Enviar correo de expiración al cliente
            try {
                Mail::to($reserva->correo_cliente)->queue(new ReservaExpiradaMail($reserva));
            } catch (\Exception $e) {
                logger()->error('Error encolando email de reserva expirada', [
                    'reserva_id' => $reserva->id,
                    'error'      => $e->getMessage(),
                ]);
            }

            // Notificación interna al mesero/admin
            $this->crearNotificacionInterna(
                $sucursal->id,
                "🚫 No-Show: Reserva {$reserva->codigo_reserva} — {$reserva->nombre_cliente} no se presentó.",
                'reservas'
            );
        }

        return $reservasExpiradas->count();
    }

    // ─── Pago de depósito ─────────────────────────────────────────

    /**
     * Registra el pago del depósito de garantía.
     * Si el pago es aprobado, mueve la reserva a PENDIENTE y auto-confirma si aplica.
     *
     * @param  array $datos {metodo, referencia, nequi_telefono?, nequi_correo?}
     */
    public function procesarPagoDeposito(ReservaMesa $reserva, array $datos, Sucursal $sucursal): Pago
    {
        if ($reserva->estado !== \App\Enums\EstadoReserva::PENDIENTE_PAGO) {
            throw ValidationException::withMessages([
                'estado' => 'Esta reserva no está esperando pago de depósito.',
            ]);
        }

        return DB::transaction(function () use ($reserva, $datos, $sucursal) {
            // Crear registro de pago
            $pago = Pago::create([
                'payable_type'   => get_class($reserva),
                'payable_id'     => $reserva->id,
                'sucursal_id'    => $reserva->sucursal_id,
                'monto'          => $reserva->monto_deposito,
                'metodo'         => $datos['metodo'],
                'estado'         => Pago::ESTADO_PENDIENTE,
                'nequi_telefono' => $datos['nequi_telefono'] ?? null,
                'nequi_correo'   => $datos['nequi_correo']   ?? null,
                'referencia'     => $datos['referencia']     ?? null,
                'notas'          => $datos['notas']          ?? null,
                'intentos'       => 1,
                'ultimo_intento_en' => now(),
            ]);

            // Para pagos en efectivo o transferencia con referencia: aprobar automáticamente
            // Para Nequi sin referencia: queda pendiente de verificación manual por el admin
            $aprobarAutomaticamente = in_array($datos['metodo'], ['efectivo']) ||
                (!empty($datos['referencia']) && $datos['metodo'] === 'transferencia');

            if ($aprobarAutomaticamente) {
                $pago->aprobar($datos['referencia'] ?? 'Efectivo en recepción');

                try {
                    Mail::to($reserva->correo_cliente)->queue(new ComprobantePagoReservaMail($pago));
                } catch (\Exception $e) {
                    logger()->error('Error encolando email de comprobante de pago', [
                        'pago_id' => $pago->id,
                        'error'   => $e->getMessage(),
                    ]);
                }

                // Tras aprobar el pago, el modelo PagoReserva ya movió la reserva a PENDIENTE.
                // Ahora auto-confirmar si está configurado.
                $reserva->refresh();
                if ($this->autoConfirmar($sucursal)) {
                    $this->confirmarReserva($reserva, $sucursal);
                } else {
                    $this->notificarAdminsDeposito($reserva, $sucursal);
                }
            } else {
                // Nequi/tarjeta: notificar al admin para verificar
                $this->notificarAdminsDeposito($reserva, $sucursal);
            }

            return $pago->fresh();
        });
    }

    /**
     * Aprueba manualmente un pago de depósito pendiente (por el admin/mesero).
     */
    public function aprobarPagoDeposito(PagoReserva $pago, string $referencia, Sucursal $sucursal): void
    {
        if ($pago->estaAprobado()) {
            throw ValidationException::withMessages([
                'pago' => 'Este pago ya está aprobado.',
            ]);
        }

        DB::transaction(function () use ($pago, $referencia, $sucursal) {
            $pago->aprobar($referencia);

            try {
                Mail::to($pago->reserva->correo_cliente)->queue(new ComprobantePagoReservaMail($pago));
            } catch (\Exception $e) {
                logger()->error('Error encolando email de comprobante de pago', [
                    'pago_id' => $pago->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            $reserva = $pago->reserva->fresh();
            if ($this->autoConfirmar($sucursal)) {
                $this->confirmarReserva($reserva, $sucursal);
            }
        });
    }

    // ─── Notificaciones internas ──────────────────────────────────

    private function notificarAdminsDeposito(ReservaMesa $reserva, Sucursal $sucursal): void
    {
        $mensaje = "💳 Depósito recibido para reserva {$reserva->codigo_reserva} — {$reserva->nombre_cliente}. Verificar pago para confirmar.";
        $this->notificarUsuariosSucursal($sucursal, $mensaje, 'reservas');
    }

    private function notificarAdminNuevaReserva(ReservaMesa $reserva, Sucursal $sucursal): void
    {
        $mensaje = "📋 Nueva reserva: {$reserva->codigo_reserva} — {$reserva->nombre_cliente} para el {$reserva->fecha_reserva->format('d/m')} a las {$reserva->hora_inicio}.";
        $this->notificarUsuariosSucursal($sucursal, $mensaje, 'reservas');
    }

    private function programarAlertaInterna(ReservaMesa $reserva): void
    {
        $minutosHastaReserva = now()->diffInMinutes($reserva->inicio, false);

        if ($minutosHastaReserva > 0 && $minutosHastaReserva <= 30) {
            $sucursal = $reserva->sucursal;
            if ($sucursal) {
                $this->notificarUsuariosSucursal(
                    $sucursal,
                    "⏰ Reserva próxima en {$minutosHastaReserva} min: {$reserva->codigo_reserva} — {$reserva->nombre_cliente} ({$reserva->numero_personas} personas).",
                    'reservas'
                );
            }
        }
    }

    /**
     * Crea notificaciones internas y Web Push para todos los admins/meseros de la sucursal.
     */
    private function notificarUsuariosSucursal(Sucursal $sucursal, string $mensaje, string $tipo): void
    {
        try {
            $usuarios = \App\Models\User::where('sucursal_id', $sucursal->id)
                ->where('activo', true)
                ->get();

            $pushService = app(WebPushService::class);

            foreach ($usuarios as $usuario) {
                // Notificación interna (campanilla)
                Notificacion::create([
                    'usuario_id' => $usuario->id,
                    'tipo'       => $tipo,
                    'titulo'     => 'Reserva de Mesa',
                    'mensaje'    => $mensaje,
                    'leida'      => false,
                ]);

                // Notificación Push al navegador (si el usuario tiene suscripción activa)
                try {
                    $pushService->sendToUser($usuario->id, 'Reserva de Mesa', $mensaje, [
                        'tipo' => $tipo,
                        'url'  => '/admin/reservas',
                    ]);
                } catch (\Throwable $pushEx) {
                    logger()->warning('[ReservaService] Push fallido para usuario ' . $usuario->id, [
                        'error' => $pushEx->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            logger()->error('Error creando notificación interna de reserva', ['error' => $e->getMessage()]);
        }
    }
}
