<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol_id',
        'estado',
        'ultimo_login',
    ];

    // ✅ AÑADIDO: remember_token debe ser hidden y nunca exponerse
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'estado'       => 'boolean',
        'ultimo_login' => 'datetime',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function sesiones()
    {
        return $this->hasMany(Sesion::class, 'usuario_id');
    }
}