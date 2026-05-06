<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Mesa extends Model
{
    protected $table = 'mesas';

    protected $fillable = [
        'numero', 'capacidad', 'estado', 'qr_codigo', 'qr_activo'
    ];

    // Constantes de estado
    const ESTADO_DISPONIBLE = 'DISPONIBLE';
    const ESTADO_OCUPADA    = 'OCUPADA';

    // Genera un código QR único para la mesa
    public static function generarCodigoQR($mesaId): string
    {
        return base64_encode('mesa:' . $mesaId . ':' . Str::random(16));
    }

    // Relaciones
    public function sesionesActivas()
    {
        return $this->hasMany(SesionMesa::class, 'mesa_id')
                    ->where('estado', 'ACTIVA');
    }

    public function sesionActiva()
    {
        return $this->hasOne(SesionMesa::class, 'mesa_id')
                    ->where('estado', 'ACTIVA')
                    ->latest();
    }

    // Helpers de estado
    public function ocupar(): void
    {
        $this->update(['estado' => self::ESTADO_OCUPADA]);
    }

    public function liberar(): void
    {
        $this->update(['estado' => self::ESTADO_DISPONIBLE]);
    }

    public function estaDisponible(): bool
    {
        return $this->estado === self::ESTADO_DISPONIBLE;
    }
}