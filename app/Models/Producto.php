<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSucursal;
use App\Traits\HasUuid;

class Producto extends Model
{
    use HasUuid, SoftDeletes, BelongsToSucursal;

    protected $table = 'productos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'sucursal_id',
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'precio_oferta',
        'imagen',
        'activo',
        'disponible',
        'permite_notas',
        'limite_minimo_adiciones',
        'limite_maximo_adiciones',
        'receta'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_oferta' => 'decimal:2',
        'activo' => 'boolean',
        'disponible' => 'boolean',
        'permite_notas' => 'boolean',
        'limite_minimo_adiciones' => 'integer',
        'limite_maximo_adiciones' => 'integer',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function detalles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DetallePedido::class, 'producto_id');
    }

    public function variantes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VarianteProducto::class, 'producto_id');
    }

    public function adiciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AdicionProducto::class, 'producto_id');
    }

    public function getDisponibleAttribute($value)
    {
        if (\Illuminate\Support\Facades\Cache::has("producto_{$this->id}_pausado")) {
            return false;
        }
        return (bool) $value;
    }

    public function getPausaExpiraAttribute()
    {
        return \Illuminate\Support\Facades\Cache::get("producto_{$this->id}_pausado");
    }

    public function getAdicionesDisponiblesAttribute()
    {
        return $this->adiciones()
            ->where('activo', true)
            ->get();
    }

    /**
     * Scope to only include active products whose category is also active (or null).
     */
    public function scopeActivoConCategoriaActiva($query)
    {
        return $query->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('categoria_id')
                  ->orWhereHas('categoria', function ($q2) {
                      $q2->where('activo', true);
                  });
            });
    }
}
