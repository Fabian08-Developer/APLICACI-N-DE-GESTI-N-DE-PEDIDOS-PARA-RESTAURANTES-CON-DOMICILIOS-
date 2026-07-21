<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class Pago extends Model
{
    use HasUuid;

    protected $table = 'pagos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    const ESTADO_PENDIENTE   = 'pendiente';
    const ESTADO_APROBADO    = 'aprobado';
    const ESTADO_RECHAZADO   = 'rechazado';
    const ESTADO_REEMBOLSADO = 'reembolsado';

    protected $fillable = [
        'payable_type',
        'payable_id',
        'sucursal_id',
        'metodo',
        'monto',
        'monto_devuelto',
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
        'ultimo_intento_en' => 'datetime',
        'aprobado_en' => 'datetime',
        'reembolsado_en' => 'datetime',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function aprobar(?string $referencia = null)
    {
        $this->estado = self::ESTADO_APROBADO;
        $this->aprobado_en = now();
        
        if ($referencia) {
            $this->referencia = $referencia;
        }

        $this->save();
        
        return $this;
    }

    public function rechazar(?string $notas = null)
    {
        $this->estado = self::ESTADO_RECHAZADO;
        
        if ($notas) {
            $this->notas = $notas;
        }

        $this->save();

        return $this;
    }
}
