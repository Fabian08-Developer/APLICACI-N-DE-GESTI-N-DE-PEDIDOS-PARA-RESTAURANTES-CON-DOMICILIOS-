<?php

namespace App\Models;

use App\Enums\EstadoPedido;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'sesion_mesa_id', 'mesero_id', 'estado', 'total',
        'fecha_cancelacion', 'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha_cancelacion' => 'datetime',
    ];

    // La sesión de mesa a la que pertenece este pedido
    public function sesionMesa()
    {
        return $this->belongsTo(SesionMesa::class, 'sesion_mesa_id');
    }

    // El mesero que tomó el pedido
    public function mesero()
    {
        return $this->belongsTo(Usuario::class, 'mesero_id');
    }

    // Los productos dentro del pedido
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    // Historial de cambios de estado
    public function historial()
    {
        return $this->hasMany(HistorialEstadoPedido::class, 'pedido_id')->latest();
    }

    // Pagos asociados al pedido
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'pedido_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de negocio
    |--------------------------------------------------------------------------
    */

    /**
     * Indica si el pedido puede ser cancelado por el cliente.
     * Solo se permite cancelar cuando está en estado CREADO (HU71).
     */
    public function esCancelable(): bool
    {
        return in_array($this->estado, [
            EstadoPedido::PENDIENTE_PAGO->value,
            EstadoPedido::CREADO->value,
        ]);
    }

    /**
     * Scope para obtener solo los pedidos que se consideran ventas exitosas.
     */
    public function scopeCompletado($query)
    {
        return $query->where('estado', EstadoPedido::ENTREGADO->value);
    }

    /**
     * Scope para filtrar pedidos por un rango de fechas.
     */
    public function scopeRangoFechas($query, $fechaInicio, $fechaFin)
    {
        if ($fechaInicio && $fechaFin) {
            // Se usa whereBetween asegurando que tome todo el día
            return $query->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        }
        return $query;
    }
}