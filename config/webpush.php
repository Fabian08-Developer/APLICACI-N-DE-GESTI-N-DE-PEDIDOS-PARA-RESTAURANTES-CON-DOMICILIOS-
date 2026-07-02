<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID Keys para Web Push Notifications
    |--------------------------------------------------------------------------
    |
    | Genera estas claves con: php artisan webpush:vapid
    | Luego pégalas en el .env como VAPID_PUBLIC_KEY y VAPID_PRIVATE_KEY.
    |
    */

    'vapid' => [
        'subject'     => env('VAPID_SUBJECT', env('APP_URL', 'mailto:admin@sgpd.local')),
        'public_key'  => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
    ],
];
