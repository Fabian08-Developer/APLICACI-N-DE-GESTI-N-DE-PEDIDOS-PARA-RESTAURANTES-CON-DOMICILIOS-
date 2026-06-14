<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class SucursalBarrioTarifa extends Model
{
    use HasUuid;

    protected $table = 'sucursal_barrio_tarifas';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sucursal_id',
        'barrio_id',
        'costo_envio',
        'tiempo_estimado',
        'activo',
    ];

    protected $casts = [
        'activo'          => 'boolean',
        'costo_envio'     => 'decimal:2',
        'tiempo_estimado' => 'integer',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function barrio(): BelongsTo
    {
        return $this->belongsTo(Barrio::class);
    }
}
