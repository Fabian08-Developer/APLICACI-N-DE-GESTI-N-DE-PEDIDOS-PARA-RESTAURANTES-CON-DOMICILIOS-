<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    // Le decimos que use la tabla "sesiones"
    protected $table = 'sesiones';

    protected $fillable = [
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
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}