<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Cliente\CarritoService;
use App\Models\SesionCliente;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Categoria;

class CarritoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $carritoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carritoService = new CarritoService();
    }

    public function test_agregar_producto_valido_al_carrito()
    {
        // Configurar base
        $empresa = \App\Models\Empresa::create([
            'nombre' => 'Test',
            'nit' => '123456',
            'correo' => 'test@test.com',
            'celular' => '123',
            'activo' => true
        ]);
        $sucursal = Sucursal::create([
            'nombre' => 'Sucursal 1',
            'empresa_id' => $empresa->id,
            'direccion' => 'Calle 1',
            'celular' => '123',
            'slug' => 'sucursal-1'
        ]);
        $categoria = Categoria::create(['nombre' => 'Cat 1', 'sucursal_id' => $sucursal->id, 'activo' => true]);
        $producto = Producto::create([
            'nombre' => 'Prod 1',
            'sucursal_id' => $sucursal->id,
            'categoria_id' => $categoria->id,
            'disponible' => true,
            'precio' => 10000,
            'activo' => true
        ]);
        
        $sesion = SesionCliente::create([
            'sucursal_id' => $sucursal->id,
            'token' => 'test-token',
            'activo' => true
        ]);

        $validated = [
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'variantes_elegidas' => [],
            'adiciones_elegidas' => [],
            'notas' => 'Sin cebolla'
        ];

        $item = $this->carritoService->agregarAlCarrito($sesion, $validated);

        $this->assertDatabaseHas('item_carritos', [
            'id' => $item->id,
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'subtotal' => 20000,
            'notas' => 'Sin cebolla'
        ]);
    }
}
