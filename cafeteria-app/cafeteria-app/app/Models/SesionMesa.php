<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToSucursal;

class SesionMesa extends Model
{
    use HasFactory, BelongsToSucursal;

    protected $table = 'sesiones_mesa';

    protected $fillable = [
        'sucursal_id',
        'mesa_id',
        'codigo_grupo',
        'tipo_sesion',
        'estado',
        'motivo_cierre',
        'participantes_activos',
        'fecha_inicio',
        'fecha_cierre',
        'token',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    // ── Constantes ──
    const ESTADO_ACTIVA  = 'ACTIVA';
    const ESTADO_CERRADA = 'CERRADA';

    const MOTIVO_MANUAL      = 'manual';
    const MOTIVO_INACTIVIDAD = 'inactividad';

    // ── Relaciones ──
    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'sesion_mesa_id');
    }

    // ── Cerrar esta sesión ──
    public function cerrar(string $motivo): void
    {
        // Cancelar pedidos activos de esta sesión para evitar "zombies"
        $this->pedidos()->whereNotIn('estado', ['ENTREGADO', 'CANCELADO'])->update([
            'estado'             => 'CANCELADO',
            'motivo_cancelacion' => 'Sesión de mesa cerrada (' . $motivo . ')',
            'fecha_cancelacion'  => now(),
        ]);

        $this->update([
            'estado'        => self::ESTADO_CERRADA,
            'motivo_cierre' => $motivo,
            'fecha_cierre'  => now(),
            'token'         => null,
        ]);

        // Solo liberar la mesa si no quedan más sesiones activas
        $otrasActivas = self::where('mesa_id', $this->mesa_id)
            ->where('estado', self::ESTADO_ACTIVA)
            ->where('id', '!=', $this->id)
            ->count();

        if ($otrasActivas === 0) {
            $this->mesa->liberar();
        }
    }
}