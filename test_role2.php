<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate Controller store logic precisely
$empresa_id = \App\Models\Empresa::first()->id ?? null;
$sucursal_id = \App\Models\Sucursal::first()->id ?? null;
$rol = 'administrador';

$newUser = \App\Models\User::create([
    'empresa_id' => $empresa_id,
    'sucursal_id' => $sucursal_id,
    'nombre' => 'Test Admin 2',
    'correo' => 'testadmin2@example.com',
    'contrasena' => bcrypt('password'),
    'activo' => true,
    'rol' => $rol,
]);

$newUser->assignRole($rol);

// Now reload from DB
$freshUser = \App\Models\User::where('correo', 'testadmin2@example.com')->first();
echo "Roles in DB: " . json_encode($freshUser->roles->pluck('name')) . "\n";
echo "Rol Attribute: " . json_encode($freshUser->rol) . "\n";
