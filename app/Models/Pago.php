<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'pedido_id', 'metodo_pago', 'monto', 'estado',
        'referencia_transaccion', 'fecha_reembolso', 'telefono', 'email',
    ];

    protected $casts = [
        'fecha_reembolso' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}