<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barrio extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'zona_id', 'activo'];

    public function zona()
    {
        return $this->belongsTo(ZonaCobertura::class, 'zona_id');
    }

    public function domiciliarios()
    {
        return $this->belongsToMany(Domiciliario::class, 'barrio_domiciliario');
    }
}
