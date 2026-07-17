<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;

class Empresa extends Model
{
    use SoftDeletes, HasUuid;

    protected $table = 'empresas';

    // Configuración para usar los timestamps en español según las migraciones
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'nit',
        'tipo_nit',
        'nombre',
        'slug',
        'apariencia',
        'direccion',
        'ciudad',
        'telefono',
        'activo',
        'documento_path',
        'documento_pendiente_path'
    ];

    protected $casts = [
        'activo'     => 'boolean',
        'apariencia' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($empresa) {
            if (empty($empresa->slug) && !empty($empresa->nombre)) {
                $empresa->slug = \Illuminate\Support\Str::slug($empresa->nombre) . '-' . substr(uniqid(), -4);
            }
        });
    }

    /**
     * Obtiene un valor de la apariencia con fallback seguro.
     */
    public function aparienciaValor(string $clave, mixed $defecto = null): mixed
    {
        return $this->apariencia[$clave] ?? $defecto;
    }

    /**
     * Relación: Una empresa tiene muchas sucursales (sedes)
     */
    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
    }

    /**
     * Relación: Una empresa tiene muchos usuarios (Gerentes/Staff)
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
