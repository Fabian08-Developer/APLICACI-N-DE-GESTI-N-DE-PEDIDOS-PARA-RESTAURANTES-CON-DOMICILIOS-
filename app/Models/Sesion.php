<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSucursal;

class Sesion extends Model
{
    use BelongsToSucursal;

    // Le decimos que use la tabla "sesiones"
    protected $table = 'sesiones';

    protected $fillable = [
        'sucursal_id',
        'usuario_id',
        'token',
        'ip',
        'user_agent',
        'fecha_expiracion',
        'activa',
    ];

    // Relación: una sesión pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}