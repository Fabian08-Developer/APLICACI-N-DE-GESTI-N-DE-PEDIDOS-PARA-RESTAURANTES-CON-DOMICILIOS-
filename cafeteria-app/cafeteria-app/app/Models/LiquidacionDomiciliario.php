<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class LiquidacionDomiciliario extends Model
{
    use HasUuid;

    protected $table = 'liquidaciones_domiciliario';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'perfil_domiciliario_id',
        'sucursal_id',
        'aprobado_por',
        'monto',
        'estado',
        'notas',
        'liquidado_en',
    ];

    protected $dates = ['liquidado_en', 'creado_en', 'actualizado_en'];

    public function perfil(): BelongsTo
    {
        return $this->belongsTo(PerfilDomiciliario::class, 'perfil_domiciliario_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
