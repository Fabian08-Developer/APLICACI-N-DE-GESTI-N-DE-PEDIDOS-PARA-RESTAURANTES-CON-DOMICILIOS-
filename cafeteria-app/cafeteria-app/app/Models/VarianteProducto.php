<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class VarianteProducto extends Model
{
    use HasUuid;

    protected $table = 'variantes_producto';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'producto_id',
        'nombre',
        'opciones',
        'obligatorio'
    ];

    protected $casts = [
        'opciones' => 'array',
        'obligatorio' => 'boolean'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
