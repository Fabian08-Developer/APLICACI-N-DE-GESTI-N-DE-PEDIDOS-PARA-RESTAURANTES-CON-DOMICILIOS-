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
        'limite_maximo_adiciones'
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

    public function adicionesAsociadas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AdicionCatalogo::class, 'adicion_producto', 'producto_id', 'adicion_id');
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
        $adicionesDirectas = $this->adicionesAsociadas()
            ->where('adiciones_catalogo.activo', true)
            ->where(function ($q) {
                $q->where('adiciones_catalogo.disponible', true)
                  ->orWhereNull('adiciones_catalogo.disponible');
            })
            ->get();
        
        $adicionesCategoria = collect();
        if ($this->categoria_id) {
            $adicionesCategoria = AdicionCatalogo::whereHas('categorias', function ($q) {
                $q->where('categorias.id', $this->categoria_id);
            })
            ->where('adiciones_catalogo.activo', true)
            ->where(function ($q) {
                $q->where('adiciones_catalogo.disponible', true)
                  ->orWhereNull('adiciones_catalogo.disponible');
            })
            ->get();
        }

        return $adicionesDirectas->merge($adicionesCategoria)->unique('id')->values();
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
