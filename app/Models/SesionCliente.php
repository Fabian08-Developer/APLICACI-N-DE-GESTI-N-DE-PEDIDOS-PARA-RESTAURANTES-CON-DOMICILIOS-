<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;
use App\Enums\EstadoPedido;

class SesionCliente extends Model
{
    use HasUuid;

    protected $table = 'sesiones_cliente';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sucursal_id',
        'mesa_id',
        'zona_id',
        'mesero_id',
        'token',
        'tipo',
        'nombre_cliente',
        'telefono_cliente',
        'correo_cliente',
        'direccion_cliente',
        'latitud',
        'longitud',
        'activo',
        'ultima_actividad_en',
    ];

    protected $casts = [
        'ultima_actividad_en' => 'datetime',
        'activo' => 'boolean',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    public function mesero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'sesion_cliente_id');
    }

    public function itemsCarrito(): HasMany
    {
        return $this->hasMany(ItemCarrito::class, 'sesion_cliente_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────

    /**
     * Sesiones activas que no han tenido actividad en las últimas $horas horas.
     * Usada por CerrarSesionesInactivasJob para detectar sesiones zombie.
     *
     * La comparación usa `actualizado_en` como fuente principal (se actualiza
     * con touch() en cada request del cliente). Si `ultima_actividad_en` está
     * disponible se prefiere esa, ya que es más semántica.
     */
    public function scopeInactivas($query, int $horas = 4)
    {
        $limite = now()->subHours($horas);

        return $query->where('activo', true)
            ->where(function ($q) use ($limite) {
                $q->where(function ($inner) use ($limite) {
                    // Tiene registro de última actividad → usarlo
                    $inner->whereNotNull('ultima_actividad_en')
                          ->where('ultima_actividad_en', '<', $limite);
                })->orWhere(function ($inner) use ($limite) {
                    // No tiene registro → usar actualizado_en como fallback
                    $inner->whereNull('ultima_actividad_en')
                          ->where('actualizado_en', '<', $limite);
                });
            });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /**
     * Indica si la sesión está inactiva según el timeout dado.
     */
    public function estaInactiva(int $horasTimeout = 4): bool
    {
        if (!$this->activo) {
            return false; // Ya cerrada
        }

        $referencia = $this->ultima_actividad_en ?? $this->actualizado_en;

        return $referencia && $referencia->lt(now()->subHours($horasTimeout));
    }

    /**
     * Registra la marca de última actividad del cliente.
     * Debe llamarse en cada request autenticado del cliente.
     */
    public function actualizarActividad(): void
    {
        $this->update(['ultima_actividad_en' => now()]);
    }

    public function cerrar(): void
    {
        // Cancelar pedidos activos de esta sesión para evitar "zombies"
        $this->pedidos()->whereNotIn('estado', [
            EstadoPedido::ENTREGADO->value,
            EstadoPedido::CANCELADO->value,
        ])->update([
            'estado'             => EstadoPedido::CANCELADO->value,
            'motivo_cancelacion' => 'Sesión de mesa cerrada (manual)',
        ]);

        // Cerrar la sesión
        $this->update([
            'activo' => false,
        ]);

        // Si es una sesión de mesa, verificar si se debe liberar
        if ($this->mesa_id) {
            $otrasActivas = self::withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->where('mesa_id', $this->mesa_id)
                ->where('activo', true)
                ->where('id', '!=', $this->id)
                ->count();

            if ($otrasActivas === 0 && $this->mesa) {
                $this->mesa->liberar();
            }
        }
    }
}
