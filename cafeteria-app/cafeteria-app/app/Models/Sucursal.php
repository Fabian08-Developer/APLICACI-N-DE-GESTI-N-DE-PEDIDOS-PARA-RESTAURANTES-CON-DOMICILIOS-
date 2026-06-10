<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;

class Sucursal extends Model
{
    use SoftDeletes, HasUuid;

    protected $table = 'sucursales';

    // Configuración para usar los timestamps en español según las migraciones
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'slug',
        'direccion',
        'ciudad',
        'telefono',
        'logo',
        'configuracion',
        'activo',
        'hora_apertura',
        'hora_cierre',
        'latitud',
        'longitud'
    ];

    /**
     * Relación: La sucursal pertenece a una Empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relación: Una sucursal tiene muchos usuarios asignados
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación: Una sucursal tiene muchos pedidos
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
