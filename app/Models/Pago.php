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

    protected $fillable = [
        'pedido_id',
        'sucursal_id',
        'metodo',
        'monto',
        'estado',
        'nequi_telefono',
        'nequi_correo',
        'referencia',
        'intentos',
        'ultimo_intento_en',
        'reembolsado_en',
    ];

    protected $casts = [
        'ultimo_intento_en' => 'datetime',
        'reembolsado_en' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }
}
