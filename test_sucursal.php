<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    App\Models\Sucursal::create([
        'empresa_id' => 'ea17bd12-1d31-46f3-8320-495f56d600bc', 
        'nombre' => 'Test Sede 2', 
        'slug' => 'test-sede-2', 
        'ciudad' => 'Bogota', 
        'telefono' => '300000', 
        'activo' => true
    ]);
    echo 'SUCCESS';
} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
