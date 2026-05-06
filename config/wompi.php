<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wompi — Configuración de la pasarela de pago
    |--------------------------------------------------------------------------
    | Modo sandbox (pruebas): https://sandbox.wompi.co/v1
    | Modo producción:        https://production.wompi.co/v1
    |
    | Las llaves se obtienen en https://comercios.wompi.co
    | Variables requeridas en .env:
    |   WOMPI_BASE_URL
    |   WOMPI_PUBLIC_KEY
    |   WOMPI_PRIVATE_KEY
    |   WOMPI_EVENTS_SECRET
    */

    'base_url'      => env('WOMPI_BASE_URL', 'https://sandbox.wompi.co/v1'),
    'public_key'    => env('WOMPI_PUBLIC_KEY', ''),
    'private_key'   => env('WOMPI_PRIVATE_KEY', ''),
    'events_secret' => env('WOMPI_EVENTS_SECRET', ''),

    /*
    | Tiempo máximo de espera de confirmación Nequi en segundos.
    | Wompi cancela automáticamente una transacción Nequi si no es aceptada
    | en 10 minutos, pero para la UI manejamos 2 minutos de polling activo.
    */
    'timeout_polling' => env('WOMPI_TIMEOUT_POLLING', 120),
];