<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class HistorialEstadoPedido extends Model
{
    use HasUuid;

    protected $table = 'historial_estado_pedido';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null; // Esta tabla no tiene actualizado_en

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'sucursal_id',
        'estado',
        'cambiado_en',
    ];

    protected $casts = [
        'cambiado_en' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }
}
