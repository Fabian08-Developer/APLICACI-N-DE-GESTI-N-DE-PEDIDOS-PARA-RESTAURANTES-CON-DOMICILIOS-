<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\PerfilDomiciliario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

class ManageUsuariosTest extends TestCase
{
    use RefreshDatabase;

    protected $empresa;
    protected $sucursal;
    protected $gerente;
    protected $administrador;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Scopes\TenantScope::setTenantId(null);

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

        $this->gerente = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Gerente Sede',
            'correo' => 'gerente@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'gerente',
            'activo' => true,
        ]);

        $this->administrador = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Admin Sede',
            'correo' => 'admin@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'administrador',
            'activo' => true,
        ]);

        // Create a valid Sesion record for the administrador user to pass VerificarAutenticacion middleware
        \App\Models\Sesion::create([
            'usuario_id' => $this->administrador->id,
            'sucursal_id' => $this->sucursal->id,
            'token' => 'test-admin-token',
            'activa' => true,
            'fecha_expiracion' => now()->addDays(1),
        ]);
    }

    /** @test */
    public function test_multi_tenant_isolation_in_user_listing()
    {
        // Create another sucursal and user
        $sucursal2 = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sucursal Norte',
            'slug' => 'sucursal-norte',
            'activo' => true,
        ]);

        $userOfOtherSucursal = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $sucursal2->id,
            'nombre' => 'Mesero Norte',
            'correo' => 'mesero.norte@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'mesero',
            'activo' => true,
        ]);

        $this->actingAs($this->administrador);

        // Test that Livewire renders only users of same sucursal
        Livewire::test(\App\Livewire\Admin\Usuarios\ManageUsuarios::class)
            ->assertViewHas('usuarios', function ($usuarios) use ($userOfOtherSucursal) {
                return !$usuarios->contains('id', $userOfOtherSucursal->id);
            });
    }

    /** @test */
    public function test_administrador_cannot_create_gerente_rf_120()
    {
        $this->actingAs($this->administrador);

        $response = $this->post(route('admin.usuarios.store') . '?_st=test-admin-token', [
            'nombre' => 'Nuevo Gerente',
            'email' => 'nuevogerente@test.com',
            'password' => 'password123',
            'rol_id' => 'gerente',
            'estado' => 'on'
        ]);

        $response->assertSessionHasErrors(['rol_id']);
        $this->assertDatabaseMissing('usuarios', ['correo' => 'nuevogerente@test.com']);
    }

    /** @test */
    public function test_administrador_cannot_create_administrador_rf_121()
    {
        $this->actingAs($this->administrador);

        $response = $this->post(route('admin.usuarios.store') . '?_st=test-admin-token', [
            'nombre' => 'Nuevo Admin',
            'email' => 'nuevoadmin@test.com',
            'password' => 'password123',
            'rol_id' => 'administrador',
            'estado' => 'on'
        ]);

        $response->assertSessionHasErrors(['rol_id']);
        $this->assertDatabaseMissing('usuarios', ['correo' => 'nuevoadmin@test.com']);
    }

    /** @test */
    public function test_administrador_can_create_mesero_cocina_and_domiciliario_rf_122()
    {
        $this->actingAs($this->administrador);

        // Create mesero
        $response = $this->post(route('admin.usuarios.store') . '?_st=test-admin-token', [
            'nombre' => 'Nuevo Mesero',
            'email' => 'nuevomesero@test.com',
            'password' => 'password123',
            'rol_id' => 'mesero',
            'estado' => 'on'
        ]);
        $response->assertRedirect(route('admin.usuarios.index') . '?_st=test-admin-token');
        $this->assertDatabaseHas('usuarios', ['correo' => 'nuevomesero@test.com', 'rol' => 'mesero']);

        // Create cocina
        $response = $this->post(route('admin.usuarios.store') . '?_st=test-admin-token', [
            'nombre' => 'Nuevo Cocinero',
            'email' => 'nuevococinero@test.com',
            'password' => 'password123',
            'rol_id' => 'cocina',
            'estado' => 'on'
        ]);
        $response->assertRedirect(route('admin.usuarios.index') . '?_st=test-admin-token');
        $this->assertDatabaseHas('usuarios', ['correo' => 'nuevococinero@test.com', 'rol' => 'cocina']);

        // Create domiciliario & check PerfilDomiciliario is created automatically
        $response = $this->post(route('admin.usuarios.store') . '?_st=test-admin-token', [
            'nombre' => 'Nuevo Repartidor',
            'email' => 'nuevorepartidor@test.com',
            'password' => 'password123',
            'rol_id' => 'domiciliario',
            'estado' => 'on'
        ]);
        $response->assertRedirect(route('admin.usuarios.index') . '?_st=test-admin-token');
        
        $domiciliarioUser = User::where('correo', 'nuevorepartidor@test.com')->first();
        $this->assertNotNull($domiciliarioUser);
        $this->assertEquals('domiciliario', $domiciliarioUser->rol->name);

        $this->assertDatabaseHas('perfiles_domiciliario', [
            'usuario_id' => $domiciliarioUser->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => 'disponible',
            'tipo_vehiculo' => 'moto',
        ]);
    }

    /** @test */
    public function test_gerente_can_create_administrador()
    {
        $this->actingAs($this->gerente);

        $response = $this->post(route('admin.usuarios.store'), [
            'nombre' => 'Admin Creado Por Gerente',
            'email' => 'admin.gerente@test.com',
            'password' => 'password123',
            'rol_id' => 'administrador',
            'estado' => 'on'
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseHas('usuarios', ['correo' => 'admin.gerente@test.com', 'rol' => 'administrador']);
    }

    /** @test */
    public function test_user_active_inactive_toggle_rf_123()
    {
        $this->actingAs($this->gerente);

        $staffUser = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Staff To Toggle',
            'correo' => 'staff.toggle@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'mesero',
            'activo' => true,
        ]);

        // Toggle to inactive
        $response = $this->post(route('admin.usuarios.store'), [
            'toggle_user_id' => $staffUser->id
        ]);
        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertFalse((bool) $staffUser->fresh()->activo);

        // Toggle back to active
        $response = $this->post(route('admin.usuarios.store'), [
            'toggle_user_id' => $staffUser->id
        ]);
        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertTrue((bool) $staffUser->fresh()->activo);
    }

    /** @test */
    public function test_perfil_domiciliario_sync_on_edit_and_delete()
    {
        $this->actingAs($this->gerente);

        // 1. Create a mesero
        $staffUser = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Staff Edit',
            'correo' => 'staff.edit@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'mesero',
            'activo' => true,
        ]);

        $this->assertDatabaseMissing('perfiles_domiciliario', ['usuario_id' => $staffUser->id]);

        // 2. Change role to domiciliario
        $response = $this->post(route('admin.usuarios.store'), [
            'user_id' => $staffUser->id,
            'nombre' => 'Staff Edit Domiciliario',
            'email' => 'staff.edit@test.com',
            'rol_id' => 'domiciliario',
            'estado' => 'on'
        ]);
        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseHas('perfiles_domiciliario', [
            'usuario_id' => $staffUser->id,
            'tipo_vehiculo' => 'moto',
            'estado' => 'disponible',
        ]);

        // 3. Change role back to mesero -> profile should be deleted
        $response = $this->post(route('admin.usuarios.store'), [
            'user_id' => $staffUser->id,
            'nombre' => 'Staff Edit Mesero',
            'email' => 'staff.edit@test.com',
            'rol_id' => 'mesero',
            'estado' => 'on'
        ]);
        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseMissing('perfiles_domiciliario', ['usuario_id' => $staffUser->id]);

        // 4. Change role to domiciliario again
        $response = $this->post(route('admin.usuarios.store'), [
            'user_id' => $staffUser->id,
            'nombre' => 'Staff Edit Domiciliario 2',
            'email' => 'staff.edit@test.com',
            'rol_id' => 'domiciliario',
            'estado' => 'on'
        ]);
        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseHas('perfiles_domiciliario', ['usuario_id' => $staffUser->id]);

        // 5. Delete user -> profile should be deleted
        $response = $this->delete(route('admin.usuarios.destroy', $staffUser->id));
        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseMissing('perfiles_domiciliario', ['usuario_id' => $staffUser->id]);
        $this->assertSoftDeleted('usuarios', ['id' => $staffUser->id]);
    }

    /** @test */
    public function test_ultimo_acceso_en_is_recorded_and_throttled_via_middleware()
    {
        $this->actingAs($this->gerente);

        // First access: ultimo_acceso_en is null
        $this->assertNull($this->gerente->ultimo_acceso_en);

        // Send a request to trigger the middleware
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $firstAccessTime = $this->gerente->fresh()->ultimo_acceso_en;
        $this->assertNotNull($firstAccessTime);

        // Access again within 5 minutes: timestamp should not change (throttled)
        Carbon::setTestNow(now()->addMinutes(3));
        $this->get(route('admin.dashboard'));
        $this->assertEquals($firstAccessTime, $this->gerente->fresh()->ultimo_acceso_en);

        // Access after 5 minutes: timestamp should be updated
        Carbon::setTestNow(now()->addMinutes(6));
        $this->get(route('admin.dashboard'));
        $this->assertNotEquals($firstAccessTime, $this->gerente->fresh()->ultimo_acceso_en);

        Carbon::setTestNow(); // Reset test time
    }
}
