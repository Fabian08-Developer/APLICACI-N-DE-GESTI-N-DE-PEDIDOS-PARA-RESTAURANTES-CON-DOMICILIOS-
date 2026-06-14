<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSucursal;
use App\Traits\HasUuid;

class ZonaCobertura extends Model
{
    use HasUuid, BelongsToSucursal;

    protected $table = 'zonas_cobertura';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'descripcion',
        'costo_envio',
        'tiempo_estimado',
        'activo'
    ];

    public function barrios(): HasMany
    {
        return $this->hasMany(Barrio::class, 'zona_id');
    }
}
