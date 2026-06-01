<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasUuid;
use App\Traits\BelongsToSucursal;

class AdicionCatalogo extends Model
{
    use HasUuid, BelongsToSucursal;

    protected $table = 'adiciones_catalogo';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'precio',
        'activo',
        'disponible'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'disponible' => 'boolean'
    ];

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'adicion_categoria', 'adicion_id', 'categoria_id');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'adicion_producto', 'adicion_id', 'producto_id');
    }

    public function getDisponibleAttribute($value)
    {
        if (\Illuminate\Support\Facades\Cache::has("adicion_{$this->id}_pausada")) {
            return false;
        }
        return (bool) $value;
    }

    public function getPausaExpiraAttribute()
    {
        return \Illuminate\Support\Facades\Cache::get("adicion_{$this->id}_pausada");
    }
}
