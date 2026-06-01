<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'activo'
    ];

    public function zona(): BelongsTo
    {
        return $this->belongsTo(ZonaCobertura::class, 'zona_id');
    }
}
