<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasUuid, SoftDeletes, HasRoles;

    protected $table = 'usuarios';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'nombre',
        'correo',
        'contrasena',
        'documento',
        'tipo_documento',
        'telefono',
        'activo',
        'rol', 
        'correo_verificado_en',
    ];

    protected $hidden = [
        'contrasena',
        'token_recuerdo',
    ];

    // Sobreescribir métodos nativos de auth para usar nuestras columnas personalizadas
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function getRememberTokenName()
    {
        return 'token_recuerdo';
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function perfilDomiciliario()
    {
        return $this->hasOne(\App\Models\PerfilDomiciliario::class, 'usuario_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id')->latest('creado_en');
    }

    // Mantiene la compatibilidad con las vistas que llamaban a $user->rol->nombre
    public function getRolAttribute()
    {
        $role = $this->roles->first();
        $roleName = $role ? $role->name : 'mesero';
        $nombre = $roleName === 'cocina' ? 'Cocina' : ($roleName === 'mesero' ? 'Mesero' : ($roleName === 'administrador' ? 'Administrador' : ucfirst(str_replace('-', ' ', $roleName))));

        return (object)[
            'name' => $roleName,
            'nombre' => $nombre,
        ];
    }

    public function getUltimoLoginAttribute()
    {
        return $this->ultimo_acceso_en;
    }

    public function getEstadoAttribute()
    {
        return (bool)$this->activo;
    }

    public function setEstadoAttribute($value)
    {
        $this->attributes['activo'] = (bool)$value;
    }

    // Email attribute alias for compatibility with standard Laravel and views
    public function getEmailAttribute()
    {
        return $this->correo;
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['correo'] = $value;
    }

    /**
     * Check if this user can manage another user based on their roles.
     */
    public function canManage(User $targetUser): bool
    {
        if ($this->id === $targetUser->id) {
            return false;
        }

        $myRole = $this->rol->name;
        $targetRole = $targetUser->rol->name;

        if ($myRole === 'super-admin') {
            return true;
        }

        if ($myRole === 'gerente') {
            return in_array($targetRole, ['administrador', 'cocina', 'mesero', 'domiciliario']);
        } elseif ($myRole === 'administrador') {
            return in_array($targetRole, ['cocina', 'mesero', 'domiciliario']);
        }

        return false;
    }
}
