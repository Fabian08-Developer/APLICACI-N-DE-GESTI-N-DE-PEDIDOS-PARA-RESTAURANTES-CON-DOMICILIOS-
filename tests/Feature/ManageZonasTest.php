<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\ZonaCobertura;
use App\Models\Barrio;
use App\Models\PerfilDomiciliario;
use App\Models\Pedido;
use App\Models\SesionCliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageZonasTest extends TestCase
{
    use RefreshDatabase;

    protected $empresa;
    protected $sucursal;
    protected $administrador;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Scopes\TenantScope::setTenantId(null);

        $this->empresa = Empresa::create([
            'nit'    => '111222333-4',
            'nombre' => 'Restaurante Test',
            'slug'   => 'restaurante-test',
            'activo' => true,
        ]);

        $this->sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre'     => 'Sede Principal',
            'slug'       => 'sede-principal',
            'activo'     => true,
        ]);

        $this->administrador = User::create([
            'empresa_id'  => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Admin Zonas',
            'correo'      => 'admin.zonas@test.com',
            'contrasena'  => bcrypt('password'),
            'rol'         => 'administrador',
            'activo'      => true,
        ]);
    }

    /** @test */
    public function test_admin_can_view_coverage_zones_rf141()
    {
        $this->actingAs($this->administrador);

        $zona = ZonaCobertura::create([
            'sucursal_id'     => $this->sucursal->id,
            'nombre'          => 'Zona Norte',
            'costo_envio'     => 4000,
            'tiempo_estimado' => 25,
            'activo'          => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Zonas\ManageZonas::class)
            ->assertViewHas('zonas', function ($zonas) use ($zona) {
                return $zonas->contains('id', $zona->id);
            });
    }

    /** @test */
    public function test_admin_can_create_zone_with_free_delivery_rf141_rf142()
    {
        $this->actingAs($this->administrador);

        Livewire::test(\App\Livewire\Admin\Zonas\ManageZonas::class)
            ->call('openCreate')
            ->set('nombre', 'Zona Centro')
            ->set('descripcion', 'Centro histórico')
            ->set('costo_envio', 0.0) // RF-142: $0 for free shipping
            ->set('tiempo_estimado', 15)
            ->set('activo', true)
            ->set('barrios', 'El Amparo, San Diego, Getsemaní')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('zonas_cobertura', [
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Zona Centro',
            'costo_envio' => 0.0,
            'activo'      => true,
        ]);

        $zona = ZonaCobertura::where('nombre', 'Zona Centro')->first();
        $this->assertNotNull($zona);

        $this->assertDatabaseHas('barrios', [
            'zona_id' => $zona->id,
            'nombre'  => 'El Amparo',
        ]);
        $this->assertDatabaseHas('barrios', [
            'zona_id' => $zona->id,
            'nombre'  => 'San Diego',
        ]);
        $this->assertDatabaseHas('barrios', [
            'zona_id' => $zona->id,
            'nombre'  => 'Getsemaní',
        ]);
    }

    /** @test */
    public function test_cannot_register_two_zones_with_same_name_in_same_branch_rf143()
    {
        $this->actingAs($this->administrador);

        ZonaCobertura::create([
            'sucursal_id'     => $this->sucursal->id,
            'nombre'          => 'Zona Repetida',
            'costo_envio'     => 1000,
            'tiempo_estimado' => 20,
            'activo'          => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Zonas\ManageZonas::class)
            ->call('openCreate')
            ->set('nombre', 'Zona Repetida')
            ->set('costo_envio', 2000)
            ->set('tiempo_estimado', 30)
            ->call('save')
            ->assertHasErrors(['nombre']);
    }

    /** @test */
    public function test_admin_can_update_zone_details_and_sync_barrios_rf141()
    {
        $this->actingAs($this->administrador);

        $zona = ZonaCobertura::create([
            'sucursal_id'     => $this->sucursal->id,
            'nombre'          => 'Zona Sur',
            'costo_envio'     => 5000,
            'tiempo_estimado' => 35,
            'activo'          => true,
        ]);

        Barrio::create([
            'zona_id' => $zona->id,
            'nombre'  => 'Barrio Viejo',
        ]);

        Livewire::test(\App\Livewire\Admin\Zonas\ManageZonas::class)
            ->call('openEdit', $zona->id)
            ->assertSet('nombre', 'Zona Sur')
            ->assertSet('barrios', 'Barrio Viejo')
            ->set('nombre', 'Zona Sur Modificada')
            ->set('costo_envio', 6000)
            ->set('barrios', 'Nuevo Barrio 1, Nuevo Barrio 2')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('zonas_cobertura', [
            'id'     => $zona->id,
            'nombre' => 'Zona Sur Modificada',
            'costo_envio' => 6000,
        ]);

        $this->assertDatabaseMissing('barrios', [
            'zona_id' => $zona->id,
            'nombre'  => 'Barrio Viejo',
        ]);

        $this->assertDatabaseHas('barrios', [
            'zona_id' => $zona->id,
            'nombre'  => 'Nuevo Barrio 1',
        ]);
    }

    /** @test */
    public function test_updating_zone_preserves_existing_barrio_coordinates_and_uuid()
    {
        $this->actingAs($this->administrador);

        $zona = ZonaCobertura::create([
            'sucursal_id'     => $this->sucursal->id,
            'nombre'          => 'Zona Georeferenciada',
            'costo_envio'     => 5000,
            'tiempo_estimado' => 30,
            'activo'          => true,
        ]);

        $barrioExistente = Barrio::create([
            'zona_id'     => $zona->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Barrio Centro',
            'latitud'     => 10.42350000,
            'longitud'    => -75.54320000,
            'activo'      => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Zonas\ManageZonas::class)
            ->call('openEdit', $zona->id)
            ->set('nombre', 'Zona Georeferenciada Actualizada')
            ->set('costo_envio', 6000)
            ->set('barrios', 'Barrio Centro, Barrio Nuevo')
            ->call('save')
            ->assertHasNoErrors();

        // El barrio existente debe conservar su ID original y sus coordenadas
        $this->assertDatabaseHas('barrios', [
            'id'       => $barrioExistente->id,
            'zona_id'  => $zona->id,
            'nombre'   => 'Barrio Centro',
            'latitud'  => 10.42350000,
            'longitud' => -75.54320000,
        ]);

        // Y el nuevo barrio debe crearse correctamente
        $this->assertDatabaseHas('barrios', [
            'zona_id' => $zona->id,
            'nombre'  => 'Barrio Nuevo',
        ]);
    }

    /** @test */
    public function test_admin_can_toggle_active_status_rf144()
    {
        $this->actingAs($this->administrador);

        $zona = ZonaCobertura::create([
            'sucursal_id'     => $this->sucursal->id,
            'nombre'          => 'Zona Eventual',
            'costo_envio'     => 1500,
            'tiempo_estimado' => 15,
            'activo'          => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Zonas\ManageZonas::class)
            ->call('toggleActivo', $zona->id);

        $this->assertFalse((bool) $zona->fresh()->activo);

        Livewire::test(\App\Livewire\Admin\Zonas\ManageZonas::class)
            ->call('toggleActivo', $zona->id);

        $this->assertTrue((bool) $zona->fresh()->activo);
    }

    /** @test */
    public function test_admin_cannot_delete_zone_with_active_orders_rf141()
    {
        $this->actingAs($this->administrador);

        $zona = ZonaCobertura::create([
            'sucursal_id'     => $this->sucursal->id,
            'nombre'          => 'Zona Con Pedidos',
            'costo_envio'     => 1500,
            'tiempo_estimado' => 15,
            'activo'          => true,
        ]);

        $sesion = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'domicilio',
            'nombre_cliente'=> 'Cliente Especial',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);

        $pedido = Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'domicilio',
            'estado'            => 'PENDIENTE',
            'zona_id'           => $zona->id,
            'total'             => 25000,
            'subtotal'          => 23500,
            'costo_envio'       => 1500,
        ]);

        Livewire::test(\App\Livewire\Admin\Zonas\ManageZonas::class)
            ->call('eliminarZona', $zona->id);

        $this->assertDatabaseHas('zonas_cobertura', [
            'id' => $zona->id,
        ]);
    }

    /** @test */
    public function test_auto_assignment_prioritizes_preferential_zone_rf145()
    {
        $this->actingAs($this->administrador);

        $zonaA = ZonaCobertura::create([
            'sucursal_id'     => $this->sucursal->id,
            'nombre'          => 'Zona A',
            'costo_envio'     => 1000,
            'tiempo_estimado' => 15,
            'activo'          => true,
        ]);

        $zonaB = ZonaCobertura::create([
            'sucursal_id'     => $this->sucursal->id,
            'nombre'          => 'Zona B',
            'costo_envio'     => 2000,
            'tiempo_estimado' => 20,
            'activo'          => true,
        ]);

        // Domiciliario 1: Zona Preferencial A
        $user1 = User::create([
            'empresa_id'  => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Dom Preferencial A',
            'correo'      => 'domA@test.com',
            'telefono'    => '3000000001',
            'contrasena'  => bcrypt('password'),
            'rol'         => 'domiciliario',
            'activo'      => true,
        ]);
        $domA = PerfilDomiciliario::create([
            'usuario_id'    => $user1->id,
            'sucursal_id'   => $this->sucursal->id,
            'zona_id'       => $zonaA->id, // Zona Preferencial
            'tipo_vehiculo' => 'moto',
            'estado'        => 'disponible',
        ]);

        // Domiciliario 2: Zona Preferencial B
        $user2 = User::create([
            'empresa_id'  => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Dom Preferencial B',
            'correo'      => 'domB@test.com',
            'telefono'    => '3000000002',
            'contrasena'  => bcrypt('password'),
            'rol'         => 'domiciliario',
            'activo'      => true,
        ]);
        $domB = PerfilDomiciliario::create([
            'usuario_id'    => $user2->id,
            'sucursal_id'   => $this->sucursal->id,
            'zona_id'       => $zonaB->id, // Zona Preferencial
            'tipo_vehiculo' => 'moto',
            'estado'        => 'disponible',
        ]);

        // Crear pedido en Zona B
        $sesion = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'domicilio',
            'nombre_cliente'=> 'Cliente Zona B',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);

        $pedido = Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'domicilio',
            'estado'            => 'PENDIENTE',
            'zona_id'           => $zonaB->id,
            'total'             => 35000,
            'subtotal'          => 33000,
            'costo_envio'       => 2000,
        ]);

        // Ejecutar autoAsignar para el pedido
        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->call('autoAsignar', $pedido->id)
            ->assertHasNoErrors();

        // Debe asignarse a domB porque tiene la Zona B como preferencial (criterio 3 de RF-188)
        $this->assertEquals($domB->id, $pedido->fresh()->perfil_domiciliario_id);
    }
}
