<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoPedido extends Model
{
    protected $table = 'historial_estado_pedidos';

    protected $fillable = [
        'pedido_id', 'estado', 'usuario_id', 'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}