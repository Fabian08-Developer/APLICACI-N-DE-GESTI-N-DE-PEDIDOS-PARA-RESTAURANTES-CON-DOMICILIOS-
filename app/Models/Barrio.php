<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\BelongsToSucursal;
use App\Traits\HasUuid;

class Barrio extends Model
{
    use HasUuid, BelongsToSucursal;

    protected $table = 'barrios';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'zona_id',
        'sucursal_id',
        'nombre',
        'latitud',
        'longitud',
        'activo',
    ];

    protected $casts = [
        'activo'   => 'boolean',
        'latitud'  => 'decimal:8',
        'longitud' => 'decimal:8',
    ];

    /** Zona de cobertura a la que pertenece este barrio */
    public function zona(): BelongsTo
    {
        return $this->belongsTo(ZonaCobertura::class, 'zona_id');
    }

    /** Tarifas de envío por sede (tabla pivote) */
    public function tarifas(): HasMany
    {
        return $this->hasMany(SucursalBarrioTarifa::class, 'barrio_id');
    }

    /** Sucursales que tienen cobertura en este barrio */
    public function sucursales(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'sucursal_barrio_tarifas', 'barrio_id', 'sucursal_id')
                    ->withPivot(['costo_envio', 'tiempo_estimado', 'activo'])
                    ->withTimestamps('creado_en', 'actualizado_en');
    }

    /**
     * Devuelve la tarifa activa de una sede específica para este barrio.
     */
    public function tarifaParaSucursal(string $sucursalId): ?SucursalBarrioTarifa
    {
        return $this->tarifas()
                    ->where('sucursal_id', $sucursalId)
                    ->where('activo', true)
                    ->first();
    }
}
