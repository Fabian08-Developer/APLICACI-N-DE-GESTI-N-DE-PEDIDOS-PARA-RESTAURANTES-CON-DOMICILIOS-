<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Mesa;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\SesionCliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageMesasTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $sucursal;
    protected $empresa;

    protected function setUp(): void
    {
        \App\Scopes\TenantScope::setTenantId(null);
        parent::setUp();

        // Create initial tenant hierarchy
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
    public function test_it_can_create_a_table_and_generates_qr_rf_107_rf_108()
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Admin\Mesas\ManageMesas::class)
            ->set('numero', 5)
            ->set('capacidad', 4)
            ->set('estado', 'disponible')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mesas', [
            'sucursal_id' => $this->sucursal->id,
            'numero' => 5,
            'capacidad' => 4,
            'estado' => 'disponible',
            'qr_activo' => true,
        ]);

        $mesa = Mesa::where('sucursal_id', $this->sucursal->id)->where('numero', 5)->first();
        $this->assertNotEmpty($mesa->codigo_qr);
    }

    /** @test */
    public function test_it_can_edit_a_table_rf_107()
    {
        $this->actingAs($this->user);

        $mesa = Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 10,
            'capacidad' => 2,
            'estado' => 'disponible',
        ]);
        $mesa->update([
            'codigo_qr' => Mesa::generarCodigoQR($mesa->id),
            'qr_activo' => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Mesas\ManageMesas::class)
            ->call('edit', $mesa->id)
            ->assertSet('numero', 10)
            ->assertSet('capacidad', 2)
            ->assertSet('estado', 'disponible')
            ->set('numero', 12)
            ->set('capacidad', 6)
            ->set('estado', 'ocupada')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mesas', [
            'id' => $mesa->id,
            'numero' => 12,
            'capacidad' => 6,
            'estado' => 'ocupada',
        ]);
    }

    /** @test */
    public function test_it_prevents_duplicate_table_numbers_in_same_branch_rf_110()
    {
        $this->actingAs($this->user);

        Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 1,
            'estado' => 'disponible',
        ]);

        // Creating duplicate number
        Livewire::test(\App\Livewire\Admin\Mesas\ManageMesas::class)
            ->set('numero', 1)
            ->set('capacidad', 4)
            ->set('estado', 'disponible')
            ->call('save')
            ->assertHasErrors(['numero']);

        // Editing to a duplicate number
        $mesa2 = Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 2,
            'estado' => 'disponible',
        ]);

        Livewire::test(\App\Livewire\Admin\Mesas\ManageMesas::class)
            ->call('edit', $mesa2->id)
            ->set('numero', 1)
            ->call('save')
            ->assertHasErrors(['numero']);
    }

    /** @test */
    public function test_it_allows_same_table_number_in_different_branches_rf_110()
    {
        $this->actingAs($this->user);

        Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 3,
            'estado' => 'disponible',
        ]);

        $otraSucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sucursal Norte',
            'slug' => 'sucursal-norte',
            'activo' => true,
        ]);

        $otroUser = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $otraSucursal->id,
            'nombre' => 'Gerente Norte',
            'correo' => 'gerente_norte@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'gerente',
            'activo' => true,
        ]);

        $this->actingAs($otroUser);
        \App\Scopes\TenantScope::setTenantId($otraSucursal->id);

        Livewire::test(\App\Livewire\Admin\Mesas\ManageMesas::class)
            ->set('numero', 3)
            ->set('capacidad', 4)
            ->set('estado', 'disponible')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mesas', [
            'sucursal_id' => $otraSucursal->id,
            'numero' => 3,
        ]);
    }

    /** @test */
    public function test_it_can_regenerate_qr_code_rf_108()
    {
        $this->actingAs($this->user);

        $mesa = Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 4,
            'estado' => 'disponible',
        ]);
        $oldQr = Mesa::generarCodigoQR($mesa->id);
        $mesa->update([
            'codigo_qr' => $oldQr,
            'qr_activo' => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Mesas\ManageMesas::class)
            ->call('regenerateQr', $mesa->id)
            ->assertHasNoErrors();

        $mesa->refresh();
        $this->assertNotEmpty($mesa->codigo_qr);
        $this->assertNotEquals($oldQr, $mesa->codigo_qr);
    }

    /** @test */
    public function test_it_prevents_deleting_table_with_active_sessions()
    {
        $this->actingAs($this->user);

        $mesa = Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 6,
            'estado' => 'disponible',
        ]);

        // Create an active session
        SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $mesa->id,
            'token' => 'test-token-active',
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Mesas\ManageMesas::class)
            ->call('delete', $mesa->id)
            ->assertHasErrors(['general']);

        $this->assertDatabaseHas('mesas', ['id' => $mesa->id]);
    }

    /** @test */
    public function test_it_allows_deleting_table_without_active_sessions()
    {
        $this->actingAs($this->user);

        $mesa = Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 7,
            'estado' => 'disponible',
        ]);

        Livewire::test(\App\Livewire\Admin\Mesas\ManageMesas::class)
            ->call('delete', $mesa->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('mesas', ['id' => $mesa->id]);
    }

    /** @test */
    public function test_it_can_download_qr_pdf_rf_109()
    {
        $this->actingAs($this->user);

        $mesa = Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 8,
            'estado' => 'disponible',
        ]);
        $mesa->update([
            'codigo_qr' => Mesa::generarCodigoQR($mesa->id),
            'qr_activo' => true,
        ]);

        $response = $this->get(route('admin.mesas.imprimir-qr', $mesa->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function test_it_denies_downloading_qr_pdf_from_another_tenant()
    {
        $this->actingAs($this->user);

        $otraSucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sucursal Norte',
            'slug' => 'sucursal-norte',
            'activo' => true,
        ]);

        $mesaDeOtraSucursal = Mesa::create([
            'sucursal_id' => $otraSucursal->id,
            'numero' => 9,
            'estado' => 'disponible',
        ]);
        $mesaDeOtraSucursal->update([
            'codigo_qr' => Mesa::generarCodigoQR($mesaDeOtraSucursal->id),
            'qr_activo' => true,
        ]);

        $response = $this->get(route('admin.mesas.imprimir-qr', $mesaDeOtraSucursal->id));

        $response->assertStatus(404);
    }
}
