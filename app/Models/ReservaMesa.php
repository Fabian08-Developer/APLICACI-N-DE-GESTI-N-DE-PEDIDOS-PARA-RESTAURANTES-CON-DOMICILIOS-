<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Traits\BelongsToSucursal;
use App\Enums\EstadoReserva;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReservaMesa extends Model
{
    use HasUuid, BelongsToSucursal;

    protected $table = 'reservas_mesa';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sucursal_id',
        'mesero_id',
        'sesion_cliente_id',
        'codigo_reserva',
        'nombre_cliente',
        'telefono_cliente',
        'correo_cliente',
        'numero_personas',
        'notas_cliente',
        'notas_internas',
        'fecha_reserva',
        'hora_inicio',
        'hora_fin',
        'estado',
        'monto_deposito',
        'deposito_pagado',
        'deposito_pagado_en',
        'cancelado_por',
        'motivo_cancelacion',
        'confirmado_en',
        'cliente_llego_en',
        'completado_en',
        'cancelado_en',
    ];

    protected $casts = [
        'fecha_reserva'      => 'date',
        'monto_deposito'     => 'decimal:2',
        'deposito_pagado'    => 'boolean',
        'deposito_pagado_en' => 'datetime',
        'confirmado_en'      => 'datetime',
        'cliente_llego_en'   => 'datetime',
        'completado_en'      => 'datetime',
        'cancelado_en'       => 'datetime',
        'creado_en'          => 'datetime',
        'actualizado_en'     => 'datetime',
        'estado'             => EstadoReserva::class,
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function mesas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Mesa::class, 'reserva_mesas', 'reserva_id', 'mesa_id');
    }

    public function mesero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function sesionCliente(): BelongsTo
    {
        return $this->belongsTo(SesionCliente::class, 'sesion_cliente_id');
    }

    public function pagosDeposito(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PagoReserva::class, 'reserva_id');
    }

    public function pagoAprobado(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PagoReserva::class, 'reserva_id')
                    ->where('estado', PagoReserva::ESTADO_APROBADO)
                    ->latestOfMany('aprobado_en');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /** Reservas activas (ocupan slot de tiempo). */
    public function scopeActivas($query)
    {
        return $query->whereIn('estado', [
            EstadoReserva::PENDIENTE->value,
            EstadoReserva::CONFIRMADA->value,
            EstadoReserva::CLIENTE_LLEGO->value,
        ]);
    }

    /** Reservas del día actual. */
    public function scopeDelDia($query, ?string $fecha = null)
    {
        return $query->where('fecha_reserva', $fecha ?? today()->toDateString());
    }

    /** Reservas confirmadas que aún no tienen check-in. */
    public function scopePendientesDeCheckin($query)
    {
        return $query->where('estado', EstadoReserva::CONFIRMADA->value);
    }

    /** Reservas que ya pasaron su ventana de tolerancia de llegada. */
    public function scopeConNoShowPendiente($query, int $toleranciaMinutos = 15)
    {
        // Usar timezone de la aplicación para evitar desajustes en servidores UTC
        $tz     = config('app.timezone', 'America/Bogota');
        $limite = now($tz)->subMinutes($toleranciaMinutos)->format('H:i:s');

        return $query
            ->where('estado', EstadoReserva::CONFIRMADA->value)
            ->where('fecha_reserva', today($tz)->toDateString())
            ->where('hora_inicio', '<=', $limite);
    }

    // ─── Helpers de negocio ───────────────────────────────────────

    /**
     * Genera un código de reserva único y legible.
     * Formato: RES-XXXXXX (6 caracteres alfanuméricos en mayúsculas)
     */
    public static function generarCodigo(): string
    {
        do {
            $codigo = 'RES-' . strtoupper(Str::random(8));
        } while (self::withoutGlobalScope(\App\Scopes\TenantScope::class)->where('codigo_reserva', $codigo)->exists());

        return $codigo;
    }

    /**
     * Devuelve el datetime completo de inicio de la reserva.
     */
    public function getInicioAttribute(): Carbon
    {
        return Carbon::parse($this->fecha_reserva->format('Y-m-d') . ' ' . $this->hora_inicio);
    }

    /**
     * Devuelve el datetime completo de fin de la reserva.
     */
    public function getFinAttribute(): Carbon
    {
        return Carbon::parse($this->fecha_reserva->format('Y-m-d') . ' ' . $this->hora_fin);
    }

    /**
     * Indica si el cliente aún está en tiempo de cancelar.
     * (hasta N minutos antes de la hora de inicio)
     */
    public function clientePuedeCancelar(int $limiteMinutos = 60): bool
    {
        if ($this->estado->esFinal()) {
            return false;
        }
        return $this->inicio->diffInMinutes(now(), false) < -$limiteMinutos;
    }

    /**
     * Indica si la reserva ya pasó su ventana de tolerancia de llegada.
     */
    public function yaExpiro(int $toleranciaMinutos = 15): bool
    {
        return $this->estado === EstadoReserva::CONFIRMADA
            && $this->inicio->addMinutes($toleranciaMinutos)->isPast();
    }

    // ─── Acciones de estado ───────────────────────────────────────

    /**
     * Confirma la reserva y registra la fecha de confirmación.
     */
    public function confirmar(): void
    {
        $this->update([
            'estado'       => EstadoReserva::CONFIRMADA->value,
            'confirmado_en'=> now(),
        ]);
    }

    /**
     * Cancela la reserva.
     *
     * @param string $motivo  Razón de la cancelación.
     * @param string $por     'cliente' o 'restaurante'
     */
    public function cancelar(string $motivo, string $por = 'restaurante'): void
    {
        $this->update([
            'estado'           => EstadoReserva::CANCELADA->value,
            'cancelado_por'    => $por,
            'motivo_cancelacion'=> $motivo,
            'cancelado_en'     => now(),
        ]);
    }

    /**
     * Registra la llegada del cliente (check-in).
     * No crea sesionCliente — eso lo hace ReservaService.
     */
    public function registrarLlegada(?string $meseroId = null): void
    {
        $this->update([
            'estado'           => EstadoReserva::CLIENTE_LLEGO->value,
            'cliente_llego_en' => now(),
            'mesero_id'        => $meseroId ?? $this->mesero_id,
        ]);
    }

    /**
     * Marca la reserva como completada (la sesión de mesa fue cerrada).
     */
    public function completar(): void
    {
        $this->update([
            'estado'       => EstadoReserva::COMPLETADA->value,
            'completado_en'=> now(),
        ]);
    }

    /**
     * Marca la reserva como NO_SHOW (job automático).
     */
    public function marcarNoShow(): void
    {
        $this->update([
            'estado'        => EstadoReserva::NO_SHOW->value,
            'cancelado_en'  => now(),
            'motivo_cancelacion' => 'Cliente no se presentó dentro del tiempo de tolerancia.',
        ]);
    }
}
