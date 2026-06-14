<?php

namespace Tests\Feature;

use App\Enums\EstadoPedido;
use App\Models\AdicionProducto;
use App\Models\Empresa;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Sesion;
use App\Models\SesionCliente;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\VarianteProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageCocinaTest extends TestCase
{
    use RefreshDatabase;

    protected $cocinaUser;
    protected $sucursal;
    protected $empresa;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Scopes\TenantScope::setTenantId(null);

        // Create tenant structure
        $this->empresa = Empresa::create([
            'nit' => '900123456-1',
            'nombre' => 'Cocina Test S.A.S',
            'activo' => true,
        ]);

        $this->sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sede Cocina',
            'slug' => 'sede-cocina',
            'activo' => true,
        ]);

        // Create cocina user
        $this->cocinaUser = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Cocinero Principal',
            'correo' => 'cocinero@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'cocina',
            'activo' => true,
        ]);

        // Setup session for token auth
        Sesion::create([
            'usuario_id' => $this->cocinaUser->id,
            'sucursal_id' => $this->sucursal->id,
            'token' => 'kitchen-token-123',
            'activa' => true,
            'fecha_expiracion' => now()->addDays(1),
        ]);
    }

    /** @test */
    public function test_only_authenticated_kitchen_staff_can_access_dashboard()
    {
        // Unauthenticated access returns 200 with token-bridge view
        $response = $this->get(route('cocina.dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('partials.token-bridge');

        // Authenticated but no token also returns 200 with token-bridge view
        $this->actingAs($this->cocinaUser);
        $response = $this->get(route('cocina.dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('partials.token-bridge');

        // Invalid token redirects to login
        $this->get(route('cocina.dashboard') . '?_st=invalid-token')->assertRedirect(route('login'));

        // Correct access
        $this->get(route('cocina.dashboard') . '?_st=kitchen-token-123')->assertStatus(200);
    }

    /** @test */
    public function test_kitchen_dashboard_renders_kanban_columns()
    {
        $this->actingAs($this->cocinaUser);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $response = $this->get(route('cocina.dashboard') . '?_st=kitchen-token-123');
        $response->assertStatus(200);
        $response->assertSee('id="col-nuevos"', false);
        $response->assertSee('id="col-prep"', false);
        $response->assertSee('id="col-listos"', false);
    }

    /** @test */
    public function test_new_orders_polling_payload_contains_variants_additions_and_notes()
    {
        $this->actingAs($this->cocinaUser);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $sesionCliente = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'mesa',
            'nombre_cliente'=> 'Mesa 1',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);

        $pedido = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesionCliente->id,
            'tipo' => 'LOCAL',
            'estado' => EstadoPedido::CREADO->value,
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Hamburguesa Especial',
            'precio' => 10000,
        ]);

        DetallePedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre_producto' => 'Hamburguesa Especial',
            'precio_unitario' => 10000,
            'cantidad' => 1,
            'subtotal' => 10000,
            'variantes_elegidas' => ['Termino' => 'Tres Cuartos'],
            'adiciones_elegidas' => ['Tocineta'],
            'notes' => 'Sin cebolla por favor', // matching column
            'notas' => 'Sin cebolla por favor',
        ]);

        $response = $this->getJson(route('cocina.pedidos.nuevos') . '?_st=kitchen-token-123');
        $response->assertStatus(200)
            ->assertJsonPath('pedidos.0.detalles.0.variantes_elegidas.Termino', 'Tres Cuartos')
            ->assertJsonPath('pedidos.0.detalles.0.adiciones_elegidas.0', 'Tocineta')
            ->assertJsonPath('pedidos.0.detalles.0.notas', 'Sin cebolla por favor');
    }

    /** @test */
    public function test_order_status_transitions_and_preparation_timers()
    {
        $this->actingAs($this->cocinaUser);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $sesionCliente = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'mesa',
            'nombre_cliente'=> 'Mesa 1',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);

        $pedido = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesionCliente->id,
            'tipo' => 'LOCAL',
            'estado' => EstadoPedido::CREADO->value,
            'subtotal' => 5000,
            'total' => 5000,
        ]);

        $this->assertNull($pedido->listo_en);

        // Invalid transition: CREADO -> LISTO directly
        $this->postJson(route('cocina.estado', [$pedido->id, EstadoPedido::LISTO->value]) . '?_st=kitchen-token-123')
            ->assertStatus(422);

        // Valid transition: CREADO -> EN_PREPARACION
        $this->postJson(route('cocina.estado', [$pedido->id, EstadoPedido::EN_PREPARACION->value]) . '?_st=kitchen-token-123')
            ->assertStatus(200);

        $pedido->refresh();
        $this->assertEquals(EstadoPedido::EN_PREPARACION->value, $pedido->estado);

        // Valid transition: EN_PREPARACION -> LISTO
        $this->postJson(route('cocina.estado', [$pedido->id, EstadoPedido::LISTO->value]) . '?_st=kitchen-token-123')
            ->assertStatus(200);

        $pedido->refresh();
        $this->assertEquals(EstadoPedido::LISTO->value, $pedido->estado);
        $this->assertNotNull($pedido->listo_en);
    }

    /** @test */
    public function test_cancellation_realtime_synchronization_endpoint()
    {
        $this->actingAs($this->cocinaUser);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $sesionCliente = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'mesa',
            'nombre_cliente'=> 'Mesa 1',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);

        $pedido1 = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesionCliente->id,
            'tipo' => 'LOCAL',
            'estado' => EstadoPedido::CREADO->value,
            'subtotal' => 5000,
            'total' => 5000,
        ]);

        $pedido2 = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesionCliente->id,
            'tipo' => 'LOCAL',
            'estado' => EstadoPedido::CREADO->value,
            'subtotal' => 6000,
            'total' => 6000,
        ]);

        // Cancel order 2
        $pedido2->update(['estado' => 'CANCELADO']);

        $response = $this->postJson(route('cocina.pedidos.verificar-estados') . '?_st=kitchen-token-123', [
            'ids' => [$pedido1->id, $pedido2->id, 'non-existent-id']
        ]);

        $response->assertStatus(200)
            ->assertJsonPath("estados.{$pedido1->id}", EstadoPedido::CREADO->value)
            ->assertJsonPath("estados.{$pedido2->id}", 'CANCELADO')
            ->assertJsonMissingPath("estados.non-existent-id");
    }

    /** @test */
    public function test_kitchen_availability_toggles()
    {
        $this->actingAs($this->cocinaUser);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Café Latte',
            'precio' => 4500,
            'disponible' => true,
        ]);

        $variante = VarianteProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Endulzante',
            'obligatorio' => false,
            'opciones' => [
                ['nombre' => 'Azúcar', 'precio' => 0.00, 'tipo_impacto' => 'incremental', 'disponible' => true],
                ['nombre' => 'Miel', 'precio' => 500, 'tipo_impacto' => 'incremental', 'disponible' => true],
            ],
        ]);

        $adicion = AdicionProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Crema Batida',
            'precio' => 1200,
            'activo' => true,
        ]);

        // Toggle product availability
        $this->post(route('cocina.disponibilidad.toggle-producto', $producto->id) . '?_st=kitchen-token-123')
            ->assertRedirect();
        $this->assertFalse((bool) $producto->fresh()->disponible);

        // Toggle variant option availability
        $this->post(route('cocina.disponibilidad.toggle-variante', [$variante->id, 'Miel']) . '?_st=kitchen-token-123')
            ->assertRedirect();
        $this->assertFalse($variante->fresh()->opciones[1]['disponible']);

        // Toggle addition availability
        $this->post(route('cocina.disponibilidad.toggle-adicion', $adicion->id) . '?_st=kitchen-token-123')
            ->assertRedirect();
        $this->assertFalse((bool) $adicion->fresh()->activo);
    }
}
