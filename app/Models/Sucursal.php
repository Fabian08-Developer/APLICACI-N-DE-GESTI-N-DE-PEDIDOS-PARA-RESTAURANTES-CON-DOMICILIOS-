<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasUuid;
use Carbon\Carbon;

class Sucursal extends Model
{
    use SoftDeletes, HasUuid;

    protected $table = 'sucursales';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'slug',
        'direccion',
        'ciudad',
        'telefono',
        'logo',
        'configuracion',
        'activo',
        'hora_apertura',
        'hora_cierre',
        'timezone',
        'latitud',
        'longitud',
    ];

    protected $casts = [
        'activo'  => 'boolean',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        static::creating(function ($sucursal) {
            if (empty($sucursal->slug) && !empty($sucursal->nombre)) {
                $sucursal->slug = \Illuminate\Support\Str::slug($sucursal->nombre) . '-' . substr(uniqid(), -4);
            }
        });
    }

    // ─── Relaciones ──────────────────────────────────────────────

    /** La sucursal pertenece a una Empresa */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /** Usuarios asignados a esta sucursal */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Pedidos de esta sucursal */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    /** Zonas de cobertura de esta sucursal */
    public function zonasCobertura(): HasMany
    {
        return $this->hasMany(ZonaCobertura::class);
    }

    /** Tarifas por barrio (tabla pivote) */
    public function tarifasBarrio(): HasMany
    {
        return $this->hasMany(SucursalBarrioTarifa::class);
    }

    /** Barrios que cubre esta sucursal (many-to-many via pivote) */
    public function barrios(): BelongsToMany
    {
        return $this->belongsToMany(Barrio::class, 'sucursal_barrio_tarifas', 'sucursal_id', 'barrio_id')
                    ->withPivot(['costo_envio', 'tiempo_estimado', 'activo'])
                    ->withTimestamps('creado_en', 'actualizado_en');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Indica si la sucursal está abierta en este momento.
     * Usa el timezone propio de la sucursal si está configurado.
     */
    public function estaAbierta(): bool
    {
        if (!$this->hora_apertura || !$this->hora_cierre) {
            return true; // Sin horario configurado → siempre abierta
        }

        $tz       = $this->timezone ?? config('app.timezone', 'America/Bogota');
        $ahora    = Carbon::now($tz)->format('H:i:s');
        $apertura = $this->hora_apertura;
        $cierre   = $this->hora_cierre;

        // Soporte para horarios que cruzan medianoche
        if ($apertura <= $cierre) {
            return $ahora >= $apertura && $ahora <= $cierre;
        }

        // Horario nocturno: ej. 22:00 – 04:00
        return $ahora >= $apertura || $ahora <= $cierre;
    }

    /**
     * Determina si la sucursal tiene las reservas de mesas activadas en su configuración.
     */
    public function tieneReservasActivas(): bool
    {
        $config = is_string($this->configuracion)
            ? json_decode($this->configuracion, true)
            : ($this->configuracion ?? []);
        return ($config['reservas_activas'] ?? true) !== false;
    }
}

