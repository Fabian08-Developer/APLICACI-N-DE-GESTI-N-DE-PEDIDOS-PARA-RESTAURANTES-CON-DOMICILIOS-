<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Reemplaza session('carrito') — el carrito ahora vive en la BD.
 *
 * Ventaja: cada sesión de mesa tiene su propio carrito independiente.
 * Antes, session('carrito') era compartida por todas las pestañas del navegador.
 * Ahora, carrito_items se filtra por sesion_mesa_id que viene del token en la URL.
 */
class CarritoItem extends Model
{
    protected $table = 'carrito_items';

    protected $fillable = [
        'sesion_mesa_id',
        'producto_id',
        'nombre',
        'precio',
        'cantidad',
        'subtotal',
    ];

    protected $casts = [
        'precio'   => 'decimal:2',
        'subtotal' => 'decimal:2',
        'cantidad' => 'integer',
    ];

    public function sesionMesa()
    {
        return $this->belongsTo(SesionMesa::class, 'sesion_mesa_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}