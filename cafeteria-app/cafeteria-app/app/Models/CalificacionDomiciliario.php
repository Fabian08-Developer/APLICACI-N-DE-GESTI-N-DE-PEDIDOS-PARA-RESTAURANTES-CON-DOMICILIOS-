<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class CalificacionDomiciliario extends Model
{
    use HasUuid;

    protected $table = 'calificaciones_domiciliario';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'pedido_id',
        'perfil_domiciliario_id',
        'cliente_id',
        'puntuacion',
        'comentario',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function perfilDomiciliario(): BelongsTo
    {
        return $this->belongsTo(PerfilDomiciliario::class, 'perfil_domiciliario_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }
}
