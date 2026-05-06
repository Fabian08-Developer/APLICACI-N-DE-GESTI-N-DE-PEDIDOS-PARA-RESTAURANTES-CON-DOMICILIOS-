<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonaCobertura extends Model
{
    use HasFactory;

    protected $table = 'zona_coberturas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'costo_envio',
        'tiempo_estimado',
        'activo',
    ];

    public function domiciliarios()
    {
        return $this->hasMany(Domiciliario::class, 'zona_id');
    }

    public function barrios()
    {
        return $this->hasMany(Barrio::class, 'zona_id');
    }
}
