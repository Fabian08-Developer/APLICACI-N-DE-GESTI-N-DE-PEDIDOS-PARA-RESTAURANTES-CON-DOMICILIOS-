<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels — SGPD
|--------------------------------------------------------------------------
|
| R-01: Todos los canales son PRIVADOS y validan pertenencia al tenant.
| Nunca se exponen datos de una empresa/sucursal a otra.
|
*/

// Canal de sucursal: eventos visibles para todo el equipo de una sucursal
// Acceden: administrador, mesero, cocina de esa sucursal
Broadcast::channel('sucursal.{sucursal_id}', function ($user, $sucursal_id) {
    return (string) $user->sucursal_id === (string) $sucursal_id;
});

// Canal personal: notificaciones dirigidas a un usuario específico
// Accede: solo el usuario dueño del canal
Broadcast::channel('user.{user_id}', function ($user, $user_id) {
    return (string) $user->id === (string) $user_id;
});

// Canal del domiciliario: asignaciones y actualizaciones de pedidos en ruta
// Accede: el domiciliario identificado por su usuario_id
Broadcast::channel('domiciliario.{user_id}', function ($user, $user_id) {
    return (string) $user->id === (string) $user_id
        && $user->hasRole('domiciliario');
});
