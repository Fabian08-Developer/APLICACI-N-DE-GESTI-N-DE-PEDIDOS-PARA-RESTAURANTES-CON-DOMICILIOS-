<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;

class SesionCliente extends Model
{
    use HasUuid;

    protected $table = 'sesiones_cliente';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sucursal_id',
        'mesa_id',
        'zona_id',
        'mesero_id',
        'token',
        'tipo',
        'nombre_cliente',
        'telefono_cliente',
        'correo_cliente',
        'direccion_cliente',
        'latitud',
        'longitud',
        'activo',
        'ultima_actividad_en',
    ];

    protected $casts = [
        'ultima_actividad_en' => 'datetime',
        'activo' => 'boolean',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    public function mesero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'sesion_cliente_id');
    }

    public function itemsCarrito(): HasMany
    {
        return $this->hasMany(ItemCarrito::class, 'sesion_cliente_id');
    }

    public function cerrar(): void
    {
        // Cancelar pedidos activos de esta sesión para evitar "zombies"
        $this->pedidos()->whereNotIn('estado', ['ENTREGADO', 'CANCELADO'])->update([
            'estado'             => 'CANCELADO',
            'motivo_cancelacion' => 'Sesión de mesa cerrada (manual)',
        ]);

        // Cerrar la sesión
        $this->update([
            'activo' => false,
        ]);

        // Si es una sesión de mesa, verificar si se debe liberar
        if ($this->mesa_id) {
            $otrasActivas = self::where('mesa_id', $this->mesa_id)
                ->where('activo', true)
                ->where('id', '!=', $this->id)
                ->count();

            if ($otrasActivas === 0 && $this->mesa) {
                $this->mesa->liberar();
            }
        }
    }
}
