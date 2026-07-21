<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find('dc20cd3d-ec67-4fb0-9fb9-e0918a0012bb');
echo "User: {$user->email}\n";
echo "empresa_id: {$user->empresa_id}\n";

$empresa = $user->empresa;
if ($empresa) {
    echo "Empresa: {$empresa->nombre}\n";
    echo "activo: " . ($empresa->activo ? 'true' : 'false') . "\n";
} else {
    echo "ERROR: empresa es NULL\n";
}
