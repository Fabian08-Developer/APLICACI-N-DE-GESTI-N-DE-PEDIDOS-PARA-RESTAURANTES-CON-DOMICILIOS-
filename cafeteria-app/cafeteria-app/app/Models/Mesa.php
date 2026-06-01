<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSucursal;
use App\Traits\HasUuid;
use Illuminate\Support\Str;

class Mesa extends Model
{
    use HasUuid, BelongsToSucursal;

    protected $table = 'mesas';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sucursal_id',
        'numero',
        'capacidad',
        'estado',
        'codigo_qr',
        'qr_activo'
    ];

    // Constantes de estado
    const ESTADO_DISPONIBLE = 'disponible';
    const ESTADO_OCUPADA    = 'ocupada';
    const ESTADO_PIDIENDO   = 'pidiendo';
    const ESTADO_ESPERANDO_PAGO = 'esperando_pago';
    const ESTADO_POR_LIBERAR = 'por_liberar';

    /**
     * Genera un código QR único para la mesa
     */
    public static function generarCodigoQR($mesaId): string
    {
        return base64_encode('mesa:' . $mesaId . ':' . Str::random(16));
    }

    public function sesionesCliente(): HasMany
    {
        return $this->hasMany(SesionCliente::class, 'mesa_id');
    }

    public function sesionActiva()
    {
        return $this->hasOne(SesionCliente::class, 'mesa_id')
                    ->where('activo', true)
                    ->latest();
    }

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
