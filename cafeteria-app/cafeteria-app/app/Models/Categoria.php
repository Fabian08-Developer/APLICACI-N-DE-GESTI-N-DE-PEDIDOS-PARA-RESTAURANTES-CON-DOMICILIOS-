<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSucursal;
use App\Traits\HasUuid;

class Categoria extends Model
{
    use HasUuid, BelongsToSucursal;

    protected $table = 'categorias';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'descripcion',
        'icono',
        'orden',
        'activo'
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    /**
     * Scope to only include active categories.
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
