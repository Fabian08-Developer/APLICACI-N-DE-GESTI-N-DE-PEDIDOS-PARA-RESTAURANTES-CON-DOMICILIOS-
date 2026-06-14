<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class DetallePedido extends Model
{
    use HasUuid;

    protected $table = 'detalle_pedido';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'sucursal_id',
        'nombre_producto',
        'precio_unitario',
        'cantidad',
        'subtotal',
        'variantes_elegidas',
        'adiciones_elegidas',
        'notas',
        'estado',
    ];

    protected $casts = [
        'variantes_elegidas' => 'json',
        'adiciones_elegidas' => 'json',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
