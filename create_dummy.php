<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\Sucursal::first();
if ($s) {
    $p = App\Models\Pedido::create([
        'sucursal_id' => $s->id,
        'tipo' => 'local',
        'estado' => 'CREADO',
        'subtotal' => 10,
        'total' => 10
    ]);
    
    App\Models\DetallePedido::create([
        'pedido_id' => $p->id,
        'sucursal_id' => $s->id,
        'producto_id' => App\Models\Producto::first()->id ?? null,
        'nombre_producto' => 'Test',
        'precio_unitario' => 10,
        'cantidad' => 1,
        'subtotal' => 10,
        'variantes_elegidas' => "", // Testing empty string instead of array
        'adiciones_elegidas' => null
    ]);
    
    echo "Created Pedido: " . $p->id . "\n";
} else {
    echo "No sucursal found.\n";
}
