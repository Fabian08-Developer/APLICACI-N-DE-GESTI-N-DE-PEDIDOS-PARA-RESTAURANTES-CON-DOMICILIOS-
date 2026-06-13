<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

/**
 * Modelo para la tabla `notificaciones` (ya existe en BD).
 * Migración: 2024_05_09_000008_create_reportes_notificaciones_tables.php
 */
class Notificacion extends Model
{
    use HasUuid;

    protected $table = 'notificaciones';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'titulo',
        'mensaje',
        'datos',
        'leida',
    ];

    protected $casts = [
        'datos'      => 'array',
        'leida'      => 'boolean',
        'creado_en'  => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
