<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::firstOrCreate(['correo' => 'testadmin@example.com'], [
    'nombre' => 'Test', 
    'contrasena' => 'test', 
    'rol' => 'administrador',
    'empresa_id' => null,
    'sucursal_id' => null
]);
$u->assignRole('administrador');
echo json_encode($u->roles->pluck('name'));
