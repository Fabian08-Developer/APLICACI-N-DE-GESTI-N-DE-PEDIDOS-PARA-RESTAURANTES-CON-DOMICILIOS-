<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $p = App\Models\Pedido::with([
        'sesionCliente.mesa',
        'mesero:id,nombre',
        'detalles.producto:id,nombre',
        'domiciliario.usuario:id,nombre',
        'zona:id,nombre',
        'historial.usuario:id,nombre',
        'pagos',
    ])->first();

    if (!$p) {
        echo "No pedidos found.\n";
        exit;
    }
    
    // Simulate pagination object
    $pedidos = App\Models\Pedido::paginate(1);

    echo "Found pedido: " . $p->id . "\n";
    $view = view('livewire.admin.pedidos.manage-pedidos', [
        'pedidos' => $pedidos,
        'mesas' => [],
        'meseros' => [],
        'domiciliarios' => collect([]),
        'zonas' => [],
        'totalPedidosHoy' => 0,
        'pendientesHoy' => 0,
        'completadosHoy' => 0,
        'cantDomiciliosActivos' => 0,
        'tab' => 'local',
        'selectedPedido' => $p
    ])->render();
    
    echo "OK render successful\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
