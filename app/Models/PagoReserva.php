<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class PagoReserva extends Model
{
    use HasUuid;

    protected $table = 'pagos_reserva';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    const ESTADO_PENDIENTE   = 'pendiente';
    const ESTADO_APROBADO    = 'aprobado';
    const ESTADO_RECHAZADO   = 'rechazado';
    const ESTADO_REEMBOLSADO = 'reembolsado';

    protected $fillable = [
        'reserva_id',
        'sucursal_id',
        'monto',
        'monto_devuelto',
        'metodo',
        'estado',
        'nequi_telefono',
        'nequi_correo',
        'referencia',
        'referencia_externa',
        'intentos',
        'ultimo_intento_en',
        'aprobado_en',
        'reembolsado_en',
        'notas',
    ];

    protected $casts = [
        'monto'            => 'decimal:2',
        'monto_devuelto'   => 'decimal:2',
        'intentos'         => 'integer',
        'ultimo_intento_en'=> 'datetime',
        'aprobado_en'      => 'datetime',
        'reembolsado_en'   => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(ReservaMesa::class, 'reserva_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function estaAprobado(): bool
    {
        return $this->estado === self::ESTADO_APROBADO;
    }

    public function estaReembolsado(): bool
    {
        return $this->estado === self::ESTADO_REEMBOLSADO;
    }

    /**
     * Aprueba el pago y actualiza la reserva correspondiente.
     */
    public function aprobar(string $referencia = null): void
    {
        $this->update([
            'estado'      => self::ESTADO_APROBADO,
            'aprobado_en' => now(),
            'referencia'  => $referencia ?? $this->referencia,
        ]);

        // Actualizar la reserva
        $this->reserva->update([
            'deposito_pagado'    => true,
            'deposito_pagado_en' => now(),
            'estado'             => \App\Enums\EstadoReserva::PENDIENTE->value,
        ]);
    }

    /**
     * Reembolsa el depósito.
     */
    public function reembolsar(float $montoDevuelto = null): void
    {
        $this->update([
            'estado'          => self::ESTADO_REEMBOLSADO,
            'monto_devuelto'  => $montoDevuelto ?? $this->monto,
            'reembolsado_en'  => now(),
        ]);
    }
}
