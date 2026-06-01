<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Mesa;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Pedido;
use App\Models\SesionCliente;
use App\Models\PerfilDomiciliario;
use App\Models\ZonaCobertura;
use App\Enums\EstadoPedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManagePedidosTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $sucursal;
    protected $empresa;

    protected function setUp(): void
    {
        \App\Scopes\TenantScope::setTenantId(null);
        parent::setUp();

        $this->empresa = Empresa::create([
            'nit' => '123456789-0',
            'nombre' => 'Empresa Test',
            'activo' => true,
        ]);

        $this->sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sucursal Central',
            'slug' => 'sucursal-central',
            'activo' => true,
        ]);

        $this->user = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Gerente Test',
            'correo' => 'gerente@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'gerente',
            'activo' => true,
        ]);
    }

    protected function tearDown(): void
    {
        \App\Scopes\TenantScope::setTenantId(null);
        parent::tearDown();
    }

    /** @test */
    public function test_it_can_list_pedidos_by_tab()
    {
        $this->actingAs($this->user);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $sesionLocal = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-local-1',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Local',
        ]);

        $pedidoLocal = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesionLocal->id,
            'tipo' => 'local',
            'estado' => EstadoPedido::CREADO->value,
            'total' => 15000,
        ]);

        $sesionDom = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-dom-1',
            'tipo' => 'domicilio',
            'nombre_cliente' => 'Cliente Domicilio',
        ]);

        $pedidoDom = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesionDom->id,
            'tipo' => 'domicilio',
            'estado' => EstadoPedido::CREADO->value,
            'total' => 25000,
        ]);

        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->assertSet('tab', 'local')
            ->assertViewHas('pedidos', function ($pedidos) use ($pedidoLocal, $pedidoDom) {
                return $pedidos->contains($pedidoLocal) && !$pedidos->contains($pedidoDom);
            })
            ->call('setTab', 'domicilio')
            ->assertSet('tab', 'domicilio')
            ->assertViewHas('pedidos', function ($pedidos) use ($pedidoLocal, $pedidoDom) {
                return !$pedidos->contains($pedidoLocal) && $pedidos->contains($pedidoDom);
            });
    }

    /** @test */
    public function test_it_filters_orders_correctly()
    {
        $this->actingAs($this->user);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $mesa = Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 4,
            'capacidad' => 4,
            'estado' => 'disponible',
        ]);

        $sesion1 = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $mesa->id,
            'token' => 'token-1',
            'tipo' => 'local',
        ]);

        $pedido1 = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesion1->id,
            'tipo' => 'local',
            'estado' => EstadoPedido::CREADO->value,
            'total' => 10000,
        ]);

        $pedido2 = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesion1->id,
            'tipo' => 'local',
            'estado' => EstadoPedido::EN_PREPARACION->value,
            'total' => 20000,
        ]);

        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->set('filtroEstado', EstadoPedido::EN_PREPARACION->value)
            ->assertViewHas('pedidos', function ($pedidos) use ($pedido1, $pedido2) {
                return !$pedidos->contains($pedido1) && $pedidos->contains($pedido2);
            })
            ->set('filtroMesa', $mesa->id)
            ->set('filtroEstado', '')
            ->assertViewHas('pedidos', function ($pedidos) use ($pedido1, $pedido2) {
                return $pedidos->contains($pedido1) && $pedidos->contains($pedido2);
            });
    }

    /** @test */
    public function test_it_handles_domicilios_activos()
    {
        $this->actingAs($this->user);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-dom',
            'tipo' => 'domicilio',
        ]);

        $pedidoActivo = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo' => 'domicilio',
            'estado' => EstadoPedido::CREADO->value,
            'total' => 12000,
        ]);

        $pedidoEntregado = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo' => 'domicilio',
            'estado' => EstadoPedido::ENTREGADO->value,
            'total' => 15000,
        ]);

        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->call('setTab', 'domicilios_activos')
            ->assertViewHas('pedidos', function ($pedidos) use ($pedidoActivo, $pedidoEntregado) {
                return $pedidos->contains($pedidoActivo) && !$pedidos->contains($pedidoEntregado);
            });
    }

    /** @test */
    public function test_it_can_assign_domiciliario_manually()
    {
        $this->actingAs($this->user);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $usuarioDriver = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Domiciliario Uno',
            'correo' => 'driver@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'domiciliario',
            'activo' => true,
        ]);

        $driver = PerfilDomiciliario::create([
            'usuario_id' => $usuarioDriver->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => 'disponible',
        ]);

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-dom-2',
            'tipo' => 'domicilio',
        ]);

        $pedido = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo' => 'domicilio',
            'estado' => EstadoPedido::CREADO->value,
        ]);

        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->set('selectedPedidoId', $pedido->id)
            ->call('asignarDomiciliario', $driver->id)
            ->assertHasNoErrors();

        $pedido->refresh();
        $this->assertEquals($driver->id, $pedido->perfil_domiciliario_id);

        $driver->refresh();
        $this->assertEquals('en_ruta', $driver->estado);
    }

    /** @test */
    public function test_it_can_change_order_status_and_records_history()
    {
        $this->actingAs($this->user);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-state-test',
            'tipo' => 'local',
        ]);

        $pedido = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo' => 'local',
            'estado' => EstadoPedido::CREADO->value,
        ]);

        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->call('cambiarEstado', $pedido->id, EstadoPedido::EN_PREPARACION->value)
            ->assertHasNoErrors();

        $pedido->refresh();
        $this->assertEquals(EstadoPedido::EN_PREPARACION->value, $pedido->estado);

        $this->assertDatabaseHas('historial_estado_pedido', [
            'pedido_id' => $pedido->id,
            'estado' => EstadoPedido::EN_PREPARACION->value,
            'usuario_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function test_it_can_cancel_order_with_valid_reason()
    {
        $this->actingAs($this->user);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-cancel-test',
            'tipo' => 'local',
        ]);

        $pedido = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo' => 'local',
            'estado' => EstadoPedido::CREADO->value,
        ]);

        // Validation error on empty reason
        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->set('selectedPedidoId', $pedido->id)
            ->set('motivoCancelacion', '')
            ->call('cancelarPedido', $pedido->id)
            ->assertHasErrors(['motivoCancelacion']);

        // Success on valid reason
        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->set('selectedPedidoId', $pedido->id)
            ->set('motivoCancelacion', 'Cliente se arrepintió')
            ->call('cancelarPedido', $pedido->id)
            ->assertHasNoErrors();

        $pedido->refresh();
        $this->assertEquals(EstadoPedido::CANCELADO->value, $pedido->estado);
        $this->assertEquals('Cliente se arrepintió', $pedido->motivo_cancelacion);

        $this->assertDatabaseHas('historial_estado_pedido', [
            'pedido_id' => $pedido->id,
            'estado' => EstadoPedido::CANCELADO->value,
            'usuario_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function test_it_isolates_orders_by_branch_tenant()
    {
        $this->actingAs($this->user);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $otraSucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sucursal Norte',
            'slug' => 'sucursal-norte',
            'activo' => true,
        ]);

        $sesion1 = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-branch-1',
            'tipo' => 'local',
        ]);

        $pedido1 = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesion1->id,
            'tipo' => 'local',
            'estado' => EstadoPedido::CREADO->value,
        ]);

        $sesion2 = SesionCliente::create([
            'sucursal_id' => $otraSucursal->id,
            'token' => 'token-branch-2',
            'tipo' => 'local',
        ]);

        $pedido2 = Pedido::create([
            'sucursal_id' => $otraSucursal->id,
            'sesion_cliente_id' => $sesion2->id,
            'tipo' => 'local',
            'estado' => EstadoPedido::CREADO->value,
        ]);

        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->assertViewHas('pedidos', function ($pedidos) use ($pedido1, $pedido2) {
                return $pedidos->contains($pedido1) && !$pedidos->contains($pedido2);
            });
    }
}
