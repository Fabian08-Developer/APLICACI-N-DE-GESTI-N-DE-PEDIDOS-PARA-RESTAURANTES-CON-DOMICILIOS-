<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Cliente\PedidoService;
use App\Models\SesionCliente;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Categoria;
use App\Models\ItemCarrito;

class PedidoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $pedidoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pedidoService = new PedidoService();
    }

    public function test_no_se_puede_confirmar_pedido_vacio()
    {
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
        $sesion = SesionCliente::create(['sucursal_id' => $sucursal->id, 'token' => 'test-token', 'activo' => true]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El carrito está vacío.');

        $this->pedidoService->confirmarPedido($sesion);
    }
}
