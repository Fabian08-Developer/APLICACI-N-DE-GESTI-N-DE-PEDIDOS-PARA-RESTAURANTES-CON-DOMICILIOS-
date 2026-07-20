<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSucursal;
use App\Traits\HasUuid;

class Pedido extends Model
{
    use SoftDeletes, HasUuid, BelongsToSucursal;

    protected $table = 'pedidos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'sucursal_id',
        'sesion_cliente_id',
        'mesero_id',
        'perfil_domiciliario_id',
        'zona_id',
        'tipo',
        'estado',
        'metodo_pago',
        'estado_pago',
        'direccion_entrega',
        'latitud_entrega',
        'longitud_entrega',
        'subtotal',
        'costo_envio',
        'total',
        'motivo_cancelacion',
        'pagado_en',
        'en_cocina_en',
        'listo_en',
        'entregado_en',
    ];

    protected $casts = [
        'pagado_en' => 'datetime',
        'en_cocina_en' => 'datetime',
        'listo_en' => 'datetime',
        'entregado_en' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
        'eliminado_en' => 'datetime',
    ];

    public function sesionCliente(): BelongsTo
    {
        return $this->belongsTo(SesionCliente::class, 'sesion_cliente_id');
    }

    public function sesionMesa(): BelongsTo
    {
        return $this->belongsTo(SesionCliente::class, 'sesion_cliente_id');
    }

    public function mesero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function domiciliario(): BelongsTo
    {
        return $this->belongsTo(PerfilDomiciliario::class, 'perfil_domiciliario_id');
    }

    public function calificacionDomiciliario(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CalificacionDomiciliario::class, 'pedido_id');
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(ZonaCobertura::class, 'zona_id');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialEstadoPedido::class, 'pedido_id')->latest('cambiado_en');
    }

    public function getShortIdAttribute()
    {
        return substr($this->id, 0, 8);
    }

    public function pagos()
    {
        return $this->morphMany(Pago::class, 'payable');
    }
}
