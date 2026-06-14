<?php

namespace App\Enums;

/**
 * Enum canónico de roles del sistema.
 *
 * Actúa como contrato entre el código PHP y los registros de Spatie/laravel-permission.
 * REGLA: ningún rol debe crearse en BD sin que exista su case correspondiente aquí.
 *
 * Uso:
 *   $user->hasRole(RolUsuario::GERENTE->value)
 *   $user->assignRole(RolUsuario::MESERO->value)
 */
enum RolUsuario: string
{
    case SUPER_ADMIN   = 'super-admin';
    case GERENTE       = 'gerente';
    case ADMINISTRADOR = 'administrador';
    case MESERO        = 'mesero';
    case COCINA        = 'cocina';
    case DOMICILIARIO  = 'domiciliario';

    /**
     * Roles que tienen acceso de gestión global (multi-sucursal).
     */
    public static function globales(): array
    {
        return [
            self::SUPER_ADMIN->value,
            self::GERENTE->value,
        ];
    }

    /**
     * Roles operativos de sucursal (ámbito de una sola sucursal).
     */
    public static function operativos(): array
    {
        return [
            self::ADMINISTRADOR->value,
            self::MESERO->value,
            self::COCINA->value,
            self::DOMICILIARIO->value,
        ];
    }

    /**
     * Roles que un gerente puede administrar.
     */
    public static function gestionablesPorGerente(): array
    {
        return [
            self::ADMINISTRADOR->value,
            self::COCINA->value,
            self::MESERO->value,
            self::DOMICILIARIO->value,
        ];
    }

    /**
     * Roles que un administrador puede gestionar.
     */
    public static function gestionablesPorAdministrador(): array
    {
        return [
            self::COCINA->value,
            self::MESERO->value,
            self::DOMICILIARIO->value,
        ];
    }
}
