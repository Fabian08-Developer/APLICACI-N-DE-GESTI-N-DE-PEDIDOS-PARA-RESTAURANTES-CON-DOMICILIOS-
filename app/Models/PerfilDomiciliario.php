<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSucursal;
use App\Traits\HasUuid;

class PerfilDomiciliario extends Model
{
    use HasUuid, BelongsToSucursal;

    protected $table = 'perfiles_domiciliario';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'sucursal_id',
        'zona_id',
        'tipo_vehiculo',
        'placa',
        'documento',
        'estado',
        'latitud',
        'longitud',
        'ultima_ubicacion_en',
        'efectivo_pendiente',
        'limite_efectivo',
        'calificacion',
        'pedidos_hoy',
        'pedidos_totales',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(ZonaCobertura::class, 'zona_id');
    }


    public function liquidaciones(): HasMany
    {
        return $this->hasMany(LiquidacionDomiciliario::class, 'perfil_domiciliario_id')
                    ->latest('creado_en');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(CalificacionDomiciliario::class, 'perfil_domiciliario_id')
                    ->latest('creado_en');
    }

    public function pedidosActivos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'perfil_domiciliario_id')
                    ->whereNotIn('estado', ['entregado', 'cancelado']);
    }

    // RF-140: Bloqueo si tiene liquidaciones pendientes o superó el límite de efectivo
    public function getTieneBloqueoAttribute(): bool
    {
        if ((float) $this->efectivo_pendiente >= (float) $this->limite_efectivo) {
            return true;
        }

        return $this->liquidaciones()
            ->where('estado', 'pendiente')
            ->exists();
    }

    public function getNombreAttribute()
    {
        return $this->usuario ? $this->usuario->nombre : 'N/A';
    }

    public function getTelefonoAttribute()
    {
        return $this->usuario ? $this->usuario->telefono : 'N/A';
    }

    public function getInicialesAttribute()
    {
        $nombre = $this->nombre;
        $words = explode(' ', $nombre);
        $initials = '';
        foreach ($words as $w) {
            if (isset($w[0])) {
                $initials .= strtoupper($w[0]);
            }
        }
        return substr($initials, 0, 2);
    }

    public function recalcularPromedio()
    {
        $promedio = $this->calificaciones()->avg('puntuacion');
        $this->calificacion = $promedio ? round($promedio, 2) : 5.00;
        $this->save();
    }
}
