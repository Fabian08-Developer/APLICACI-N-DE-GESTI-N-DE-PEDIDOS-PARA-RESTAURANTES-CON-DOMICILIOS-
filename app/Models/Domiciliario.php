<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domiciliario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'vehiculo_tipo',
        'placa',
        'documento',
        'calificacion',
        'zona_id',
        'estado',
        'pedidos_hoy',
        'pedidos_totales',
    ];

    public function zona()
    {
        return $this->belongsTo(ZonaCobertura::class, 'zona_id');
    }

    public function barrios()
    {
        return $this->belongsToMany(Barrio::class, 'barrio_domiciliario');
    }

    /**
     * Obtener las iniciales para el avatar
     */
    public function getInicialesAttribute()
    {
        $nombres = explode(' ', $this->nombre);
        $iniciales = '';
        foreach ($nombres as $n) {
            $iniciales .= strtoupper(substr($n, 0, 1));
            if (strlen($iniciales) >= 3) break;
        }
        return $iniciales ?: 'DOM';
    }

    /**
     * Mapeo de colores para estados
     */
    public function getEstadoColorAttribute()
    {
        return match ($this->estado) {
            'disponible'     => 'success',
            'en_ruta'        => 'accent',
            'ocupado'        => 'warning',
            'fuera_servicio' => 'destructive',
            default          => 'muted',
        };
    }
}
