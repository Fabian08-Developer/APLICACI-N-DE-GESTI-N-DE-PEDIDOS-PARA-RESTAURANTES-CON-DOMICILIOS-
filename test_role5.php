<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::create([
    'correo' => 'newuser_' . time() . '@example.com',
    'nombre' => 'Test', 
    'contrasena' => 'test', 
    'rol' => 'administrador',
    'empresa_id' => null,
    'sucursal_id' => null
]);
var_dump($u->id);
$u->assignRole('administrador');
echo "Done";
