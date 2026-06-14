<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class ItemCarrito extends Model
{
    use HasUuid;

    protected $table = 'items_carrito';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sesion_cliente_id',
        'producto_id',
        'sucursal_id',
        'nombre_producto',
        'precio_unitario',
        'cantidad',
        'subtotal',
        'variantes_elegidas',
        'adiciones_elegidas',
        'notas',
    ];

    protected $casts = [
        'variantes_elegidas' => 'json',
        'adiciones_elegidas' => 'json',
    ];

    public function sesionCliente(): BelongsTo
    {
        return $this->belongsTo(SesionCliente::class, 'sesion_cliente_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }
}
